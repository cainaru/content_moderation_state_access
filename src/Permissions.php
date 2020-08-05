<?php

namespace Drupal\content_moderation_state_access;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\workflows\Entity\Workflow;

/**
 * Defines a class for dynamic permissions based on states.
 */
class Permissions {

  use StringTranslationTrait;

  /**
   * Returns an array of edit permissions.
   *
   * @return array
   *   The edit permissions.
   */
  public function editPermissions() {
    // @todo write a test for this.
    $perms = [];
    $workflows = Workflow::loadMultipleByType('content_moderation');

    $states = \Drupal::config('workflows.workflow.editorial')->get('type_settings.states');

    foreach ($states as $id => $state) {
      $perms['edit content in the ' . $id . ' state'] = [
        'title' => $this->t('Edit content when in the %state_name state.', [
          '%state_name' => $state['label'],
        ]),
      ];
    }

    return $perms;
  }

  /**
   * Returns an array of view permissions.
   *
   * @return array
   *   The view permissions.
   */
  public function viewPermissions() {
    // @todo write a test for this.
    $perms = [];

    $workflows = Workflow::loadMultipleByType('content_moderation');
    $states = \Drupal::config('workflows.workflow.editorial')->get('type_settings.states');

    foreach ($states as $id => $state) {
      $perms['view content in the ' . $id . ' state'] = [
        'title' => $this->t('View content when in the %state_name state.', [
          '%state_name' => $state['label'],
        ]),
      ];
    }

    return $perms;
  }

}
