<?php

namespace Drupal\sitefarm_seed;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ProfileInstall.
 *
 * Helper utility to break out profile install procedural code in hooks
 *
 * @package Drupal\sitefarm
 */
class ProfileInstall {

  /**
   * Instance of the Entity Type Manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ProfileInstall constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Set defaults and hide the region settings
   *
   * @param array $form
   * @param string $country
   * @param string $timezone
   */
  public function hideAndSetDefaultRegion(array &$form, $country = 'US', $timezone = 'America/Los_Angeles') {
    $form['regional_settings']['#type'] = 'hidden';
    $form['regional_settings']['site_default_country']['#default_value'] = $country;
    $form['regional_settings']['date_default_timezone']['#default_value'] = $timezone;
  }

  /**
   * Remove update options
   *
   * @param array $form
   */
  public function removeUpdateNotificationOptions(array &$form) {
    unset($form['update_notifications']);
  }

  /**
   * Use site mail for the contact form
   *
   * @see useMailInContactFormSubmit()
   */
  public function useMailInContactForm() {
    $form['#submit'][] = [$this, 'useMailInContactFormSubmit'];
  }

  /**
   * Element #submit callback function. Use site mail for the contact form
   *
   * @see setPrimaryImageTitleText()
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function useMailInContactFormSubmit(array $form, FormStateInterface $form_state) {
    $site_mail = $form_state->getValue('site_mail');
    /** @var \Drupal\contact\Entity\ContactForm $contact_form */
    $contact_form = $this->entityTypeManager->getStorage('contact_form');
    $contact_form->load('feedback')->setRecipients([$site_mail])->trustData()->save();
  }

  /**
   * Clear all 'notification' type messages that may have been set.
   */
  public function clearMessages() {
    drupal_get_messages('status', TRUE);
    drupal_get_messages('warning', TRUE);
    drupal_get_messages('completed', TRUE);
  }

  /**
   * Uninstall module used for site install but not needed for day to day.
   */
  public function defaultContentModuleCleanup() {
    \Drupal::service('module_installer')->uninstall([
      'default_content',
      'rest',
      'hal',
      'serialization',
    ]);
  }

}
