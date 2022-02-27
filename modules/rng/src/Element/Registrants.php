<?php

namespace Drupal\rng\Element;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\rng\Form\RegistrantFields;
use Drupal\user\Entity\User;
use Drupal\rng\RegistrantsElementUtility as RegistrantsElement;

/**
 * Provides a form element for a registrant and person association.
 *
 * Properties:
 * - #event: The associated event entity.
 *
 * Usage example:
 * @code
 * $form['registrants'] = [
 *   '#type' => 'registrants',
 *   '#event' => $event_entity,
 *   '#registration' => $registration,
 * ];
 * @endcode
 *
 * @FormElement("registrants")
 */
class Registrants extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processIdentityElement'],
      ],
      '#element_validate' => [
        [$class, 'validateIdentityElement'],
        [$class, 'validateRegisterable'],
        [$class, 'validateRegistrantCount'],
        ['\Drupal\rng\Form\RegistrantFields', 'validateForm'],
      ],
      '#pre_render' => [
        [$class, 'preRenderRegistrants'],
      ],
      // Required.
      '#event' => NULL,
      '#registration' => NULL,
      '#attached' => [
        'library' => ['rng/rng.elements.registrants'],
      ],
      // Use container so classes are applied.
      '#theme_wrappers' => ['container'],
      // Allow creation of which entity types + bundles:
      // Array of bundles keyed by entity type.
      '#allow_creation' => [],
      // Allow referencing existing entity types + bundles:
      // Array of bundles keyed by entity type.
      '#allow_reference' => [],
      // Minimum number of registrants (integer), or NULL for no minimum.
      // DEPRECATED - DETERMINED BY REGISTRATION OBJECT.
      '#registrants_minimum' => NULL,
      // Maximum number of registrants (integer), or NULL for no maximum.
      // DEPRECATED - DETERMINED BY REGISTRATION OBJECT.
      '#registrants_maximum' => NULL,
      // Get form display modes used when creating entities inline.
      // An array in the format: [entity_type][bundle] = form_mode_id.
      '#form_modes' => [],
    ];
  }

  /**
   * Process the registrant element.
   *
   * @param array $element
   *   An associative array containing the form structure of the element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   An associative array containing the structure of the form.
   *
   * @return array
   *   The new form structure for the element.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public static function processIdentityElement(array &$element, FormStateInterface $form_state, array &$complete_form) {
    if (!isset($element['#event'])) {
      throw new \InvalidArgumentException('Element is missing #event property.');
    }
    if (!$element['#event'] instanceof EntityInterface) {
      throw new \InvalidArgumentException('#event for element is not an entity.');
    }

    /** @var \Drupal\rng\Entity\RegistrationInterface $registration */
    $registration = $element['#registration'];
    $event_meta = $registration->getEventMeta();
    $event_type = $event_meta->getEventType();
    $allow_anon = $event_type->getAllowAnonRegistrants();
    if (!$allow_anon && empty($element['#allow_creation']) && empty($element['#allow_reference'])) {
      throw new \InvalidArgumentException('Element cannot create or reference any entities.');
    }

    // Supporting services.
    /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository */
    $entity_display_repository = \Drupal::service('entity_display.repository');
    /** @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundle_info */
    $bundle_info = \Drupal::service('entity_type.bundle.info');
    /** @var \Drupal\Core\Entity\EntityFormBuilder $entity_form_builder */
    $entity_form_builder = \Drupal::service('entity.form_builder');

    $parents = $element['#parents'];

    $event = $element['#event'];

    $ajax_wrapper_id_root = 'ajax-wrapper-' . implode('-', $parents);

    $element['#tree'] = TRUE;
    $element['#identity_element_root'] = TRUE;
    $element['#prefix'] = '<div id="' . $ajax_wrapper_id_root . '">';
    $element['#suffix'] = '</div>';

    /** @var \Drupal\rng\Entity\RegistrantInterface[] $people */
    $people = $element['#value'];

    $ajax_wrapper_id_people = 'ajax-wrapper-people-' . implode('-', $parents);

    $element['people'] = [
      '#prefix' => '<div id="' . $ajax_wrapper_id_people . '">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    ];

    $counter = 0;
    foreach ($people as $reg_id => $registrant) {
      $counter++;
      $curr_parent = array_merge($parents, [$counter]);
      /** @var \Drupal\rng\Form\RegistrantFields $helper */
      $reg_form = [
        '#parents' => $curr_parent,
        '#reg_counter' => $counter,
        '#reg_id' => $reg_id,
      ];
      $helper = new RegistrantFields($reg_form, $form_state, $registrant);

      $reg_form = $helper->getFields($reg_form, $form_state, $registrant);
      $row = [
        '#type' => 'fieldset',
        '#title' => 'Attendee ' . $counter . ' - ' . '<a href="/user">' . $registrant->label() . '</a>',
        '#open' => TRUE,
        '#parents' => $curr_parent,
        'registrant' => $reg_form,
        '#wrapper_attributes' => [
          'class' => ['registrant-grid'],
        ],
      ];
      $row['registrant']['#attributes']['class'][] = 'registrant-grid';
      $element['people'][] = $row;
    }

    if ($registration->canAddRegistrants()) {
      $person_subform = &$element['entities']['person'];

      $person_subform['new_person'] = [
        '#type' => 'details',
        '#open' => TRUE,
        '#tree' => TRUE,
        '#title' => t('New @entity_type', ['@entity_type' => 'Registrant']),
        '#identity_element_create_container' => TRUE,
      ];

      if (count($people)) {
        // Add New button.
        $person_subform['new_person']['load_create_form'] = [
          '#type' => 'submit',
          '#value' => t('Create new @label', ['@label' => $bundle_info->getBundleInfo('registrant')[$event_type->getDefaultRegistrantType()]['label']]),
          '#ajax' => [
            'callback' => [static::class, 'ajaxElementRoot'],
            'wrapper' => $ajax_wrapper_id_root,
          ],
          '#validate' => [
            [static::class, 'decoyValidator'],
          ],
          '#submit' => [
            [static::class, 'submitToggleCreateEntity'],
          ],
          '#toggle_create_entity' => TRUE,
          '#limit_validation_errors' => [],
        ];

      }
      else {
        // Set form.
        $person_subform['new_person']['newentityform'] = [
          '#tree' => TRUE,
          '#parents' => array_merge($parents,
            ['entities', 'person', 'new_person', 'newentityform']),
        ];

        /** @var \Drupal\rng\RegistrantFactoryInterface $registrant_factory */
        $registrant_factory = \Drupal::service('rng.registrant_factory');
        $new_person = $registrant_factory->createRegistrant([
          'event' => $event,
        ]);
        $new_person
          ->setRegistration($registration);
        $display = $entity_display_repository->getFormDisplay('registrant', $new_person->bundle());
        $display->buildForm($new_person, $person_subform['new_person']['newentityform'], $form_state);

        $person_subform['new_person']['actions'] = [
          '#type' => 'actions',
          '#weight' => 10000,
        ];
        $person_subform['new_person']['actions']['create'] = [
          '#type' => 'submit',
          '#value' => t('Create and add to registration'),
          '#ajax' => [
            'callback' => [static::class, 'ajaxElementRoot'],
            'wrapper' => $ajax_wrapper_id_root,
          ],
          '#limit_validation_errors' => [
            array_merge($parents, ['entities', 'person', 'registrant']),
            array_merge($parents, ['entities', 'person', 'new_person']),
          ],
          '#validate' => [
            [static::class, 'validateCreate'],
          ],
          '#submit' => [
            [static::class, 'submitCreate'],
          ],
        ];

        $person_subform['new_person']['actions']['cancel'] = [
          '#type' => 'submit',
          '#value' => t('Cancel'),
          '#ajax' => [
            'callback' => [static::class, 'ajaxElementRoot'],
            'wrapper' => $ajax_wrapper_id_root,
          ],
          '#limit_validation_errors' => [],
          '#toggle_create_entity' => FALSE,
          '#validate' => [
            [static::class, 'decoyValidator'],
          ],
          '#submit' => [
            [static::class, 'submitToggleCreateEntity'],
          ],
        ];

      }
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    $parents = $element['#parents'];
    $value = $form_state->get($parents);

    if ($value === NULL) {
      return $element['#default_value'] ?? [];
    }

    return $value;
  }

  /**
   * An empty form validator.
   *
   * This validator is used to prevent top level form validators from running.
   * Submission elements must have a dummy validator, not just an empty
   * #validate property.
   *
   * See \Drupal\Core\Form\FormValidator::executeValidateHandlers for the
   * critical core operation details.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function decoyValidator(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Generic validator for the element.
   */
  public static function validateIdentityElement(&$element, FormStateInterface $form_state, &$complete_form) {
    $utility = new RegistrantsElement($element, $form_state);

    $registrants = $element['#value'];

    // Store original form submission in temporary values.
    $values = $form_state->getValue($element['#parents']);
    $form_state->setTemporaryValue(array_merge(['_registrants_values'], $element['#parents']), $values);

    // Change element value to registrant entities.
    $form_state->setValueForElement($element, $registrants);
  }

  /**
   * Validate whether all existing registrants are register-able.
   *
   * An identity may have been registered by another registration while
   * it is also stored in the state of another registration.
   */
  public static function validateRegisterable(&$element, FormStateInterface $form_state, &$complete_form) {
    $utility = new RegistrantsElement($element, $form_state);

    // Add existing registrants to whitelist.
    foreach ($element['#default_value'] as $existing_registrant) {
      $identity = $existing_registrant->getIdentity();
      if ($identity) {
        $utility->addWhitelistExisting($identity);
      }
    }

    /** @var \Drupal\rng\Entity\RegistrantInterface[] $registrants */
    $registrants = $element['#value'];
    $whitelisted = $utility->getWhitelistExisting();

    $identities = [];
    foreach ($registrants as $registrant) {
      $identity = $registrant->getIdentity();
      if ($identity) {
        $entity_type = $identity->getEntityTypeId();
        $id = $identity->id();
        // Check if identity can skip existing revalidation. This needs to be
        // done when the identity was created by this element.
        if (!isset($whitelisted[$entity_type][$id])) {
          $identities[$entity_type][$id] = $identity->label();
        }
      }
    }

    /** @var \Drupal\rng\EventManagerInterface $event_manager */
    $event_manager = \Drupal::service('rng.event_manager');
    $event = $element['#event'];
    $event_meta = $event_manager->getMeta($event);
    foreach ($identities as $entity_type => $identity_labels) {
      $registerable = $event_meta->identitiesCanRegister($entity_type, array_keys($identity_labels));
      // Flip identity entity IDs to array keys.
      $registerable = array_flip($registerable);
      foreach (array_diff_key($identities[$entity_type], $registerable) as $id => $label) {
        $form_state->setError($element, t('%name cannot register for this event.', [
          '%name' => $label,
        ]));
      }
    }
  }

  /**
   * Validate whether there are sufficient quantity of registrants.
   */
  public static function validateRegistrantCount(&$element, FormStateInterface $form_state, &$complete_form) {
    /** @var \Drupal\rng\Entity\RegistrantInterface[] $registrants */
    $registrants = $element['#value'];
    $count = count($registrants);

    if (isset($element['#registrants_minimum'])) {
      $minimum = $element['#registrants_minimum'];
      if ($count < $minimum) {
        $form_state->setError($element, t('There are not enough registrants on this registration. There must be at least @minimum registrants.', [
          '@minimum' => $minimum,
        ]));
      }
    }

    if (isset($element['#registrants_maximum'])) {
      $maximum = $element['#registrants_maximum'];
      if ($count > $maximum) {
        $form_state->setError($element, t('There are too many registrants on this registration. There must be at most @maximum registrants.', [
          '@maximum' => $maximum,
        ]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preRenderRegistrants($element) {
    $element['#attributes']['class'][] = 'registrants-element';
    return $element;
  }

  /**
   * Ajax callback to return the entire element.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The entire element sub-form.
   */
  public static function ajaxElementRoot(array $form, FormStateInterface $form_state) {
    return RegistrantsElement::findElement($form, $form_state);
  }

  /**
   * Validate adding myself sub-form.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function validateMyself(array &$form, FormStateInterface $form_state) {
    $element = RegistrantsElement::findElement($form, $form_state);
    $utility = new RegistrantsElement($element, $form_state);
    $utility->buildRegistrant(TRUE);
  }

  /**
   * Validate adding existing entity sub-form.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function validateExisting(array &$form, FormStateInterface $form_state) {
    $element = RegistrantsElement::findElement($form, $form_state);
    $utility = new RegistrantsElement($element, $form_state);

    $utility->buildRegistrant(TRUE);

    $autocomplete_tree = array_merge($element['#parents'],
      ['entities', 'person', 'existing', 'existing_autocomplete']);

    $element_existing = NestedArray::getValue($element,
      ['entities', 'person', 'existing', 'existing_autocomplete']);
    $existing_entity_type = $element_existing['#target_type'];
    $existing_value = NestedArray::getValue($form_state->getTemporaryValue('_registrants_values'), $autocomplete_tree);

    if (!empty($existing_value)) {
      $identity = \Drupal::entityTypeManager()->getStorage($existing_entity_type)
        ->load($existing_value);
      if ($utility->identityExists($identity)) {
        $form_state->setError(NestedArray::getValue($form, $autocomplete_tree), t('Person is already on this registration.'));
      }
    }
    else {
      $form_state->setError(NestedArray::getValue($form, $autocomplete_tree), t('Choose a person.'));
    }
  }

  /**
   * Validate identity creation sub-form.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function validateCreate(array &$form, FormStateInterface $form_state) {
    $element = RegistrantsElement::findElement($form, $form_state);
    $utility = new RegistrantsElement($element, $form_state);

    $utility->buildRegistrant(TRUE);

    $new_person_tree = array_merge($element['#parents'],
      ['entities', 'person', 'new_person', 'newentityform']);
    $subform_newentity = NestedArray::getValue($form, $new_person_tree);

    $value = $form_state->getTemporaryValue(array_merge(['_registrants_values'], $element['#parents']));
    $form_state->setValue($element['#parents'], $value);

    $new_person = $form_state->get('newentity__entity');
    $form_display = $form_state->get('newentity__form_display');
    $form_display->extractFormValues($new_person, $subform_newentity, $form_state);
    $form_display->validateFormValues($new_person, $subform_newentity, $form_state);

    /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $violations */
    $violations = $new_person->validate();
    if ($violations->count() == 0) {
      $form_state->set('newentity__entity', $new_person);
    }
    else {
      $triggering_element = $form_state->getTriggeringElement();
      foreach ($violations as $violation) {
        $form_state->setError($triggering_element, (string) $violation->getMessage());
      }
    }
  }

  /**
   * Submission callback to change the registrant from the default people.
   *
   * @param array $form
   *   The current form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function submitChangeDefault(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild();

    $element = RegistrantsElement::findElement($form, $form_state);
    $utility = new RegistrantsElement($element, $form_state);

    $utility->setChangeIt(TRUE);

  }

  /**
   * Submission callback to close the selection interface.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function submitClose(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild();

    $element = RegistrantsElement::findElement($form, $form_state);
    $utility = new RegistrantsElement($element, $form_state);

    $utility->setChangeIt(FALSE);
    $utility->clearPeopleFormInput();
  }

  /**
   * Submission callback for referencing the current user.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function submitMyself(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild();

    $element = RegistrantsElement::findElement($form, $form_state);
    $utility = new RegistrantsElement($element, $form_state);

    $registrant = $utility->buildRegistrant();
    $utility->clearPeopleFormInput();

    $current_user = \Drupal::currentUser();
    if ($current_user->isAuthenticated()) {
      $person = User::load($current_user->id());
      $registrant->setIdentity($person);
    }
  }

  /**
   * Submission callback for existing entities.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function submitExisting(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild();

    $element = RegistrantsElement::findElement($form, $form_state);
    $utility = new RegistrantsElement($element, $form_state);

    $registrant = $utility->buildRegistrant();
    $utility->clearPeopleFormInput();

    $autocomplete_tree = array_merge($element['#parents'],
      ['entities', 'person', 'existing', 'existing_autocomplete']);
    $existing_value = NestedArray::getValue($form_state->getTemporaryValue('_registrants_values'), $autocomplete_tree);

    $subform_autocomplete = NestedArray::getValue($form, $autocomplete_tree);
    $existing_entity_type = $subform_autocomplete['#target_type'];
    $person = \Drupal::entityTypeManager()->getStorage($existing_entity_type)
      ->load($existing_value);
    $registrant->setIdentity($person);

  }

  /**
   * Submission callback for creating new entities.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function submitCreate(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild();

    $element = RegistrantsElement::findElement($form, $form_state);
    $utility = new RegistrantsElement($element, $form_state);

    // New entity.
    $new_entity_tree = array_merge($element['#parents'],
      ['entities', 'person', 'new_person', 'newentityform']);
    $subform_new_entity = NestedArray::getValue($form, $new_entity_tree);

    // Save the entity.
    /** @var \Drupal\Core\Entity\EntityInterface $new_person */
    $new_person = $form_state->get('newentity__entity');
    $display = $form_state->get('newentity__form_display');

    $value = $form_state->getTemporaryValue(array_merge(['_registrants_values'], $element['#parents']));
    $form_state->setValue($element['#parents'], $value);
    $display->extractFormValues($new_person, $subform_new_entity, $form_state);
    $new_person->save();
    $utility->addWhitelistExisting($new_person);

    $registrant = $utility->buildRegistrant();
    $utility->clearPeopleFormInput();

    $registrant->setIdentity($new_person);

  }

  /**
   * Submission callback for toggling the create sub-form.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function submitToggleCreateEntity(array $form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $form_state->setRebuild();

    $element = RegistrantsElement::findElement($form, $form_state);
    $utility = new RegistrantsElement($element, $form_state);
    $utility->setShowCreateEntitySubform($trigger['#toggle_create_entity']);
  }

  /**
   * Submission callback for removing a registrant.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function submitRemovePerson(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild();

    $element = RegistrantsElement::findElement($form, $form_state);
    $utility = new RegistrantsElement($element, $form_state);

    $trigger = $form_state->getTriggeringElement();
    $row = $trigger['#identity_element_registrant_row'];

    $registrants = $utility->getRegistrants();
    unset($registrants[$row]);
    $utility->setRegistrants($registrants);
  }

}
