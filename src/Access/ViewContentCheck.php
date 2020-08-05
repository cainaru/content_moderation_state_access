<?php

/**
 * @file
 * Contains \Drupal\content_moderation_state_access\Access\ViewContentCheck.
 */

namespace Drupal\content_moderation_state_access\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessCheck;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\content_moderation\ModerationInformationInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides an access checker for viewing content in content moderation states.
 */
class ViewContentCheck extends EntityAccessCheck {

  /**
   * The moderation information service.
   *
   * @var \Drupal\content_moderation\ModerationInformationInterface
   */
  protected $moderationInfo;

  /**
   * Constructs a new ViewContentCheck.
   *
   * @param \Drupal\content_moderation\ModerationInformationInterface $moderation_information
   *   The moderation information service.
   */
  public function __construct(ModerationInformationInterface $moderation_information) {
    $this->moderationInfo = $moderation_information;
  }

  /**
   * Checks that the user has the view permissions for the state of the content.
   *
   * This checker assumes the presence of an '_entity_access' requirement key
   * in the same form as used by EntityAccessCheck.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The parametrized route.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   *
   * @see EntityAccessCheck
   */
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {
    // Split the entity type and the operation.
    $requirement = $route->getRequirement('_entity_access');
    list($entity_type, $operation) = explode('.', $requirement);

    // Only act on view operations.
    if ($operation != 'view') {
      return parent::access($route, $route_match, $account);
    }

    // If there is valid entity of the given entity type, check its access.
    $parameters = $route_match->getParameters();
    if ($parameters->has($entity_type)) {
      $entity = $parameters->get($entity_type);
      if ($this->moderationInfo->isModeratedEntity($entity)) {
        $current_state = $entity->moderation_state->value;
        if (!$account->hasPermission('view content in the ' . $current_state . ' state')) {
          return AccessResult::forbidden()->addCacheableDependency($entity);
        }
      }
    }
    // No opinion, so other access checks should decide if access should be
    // allowed or not.
    return parent::access($route, $route_match, $account);
  }

}
