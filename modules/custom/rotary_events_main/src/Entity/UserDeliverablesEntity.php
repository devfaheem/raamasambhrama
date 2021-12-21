<?php

namespace Drupal\rotary_events_main\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the User deliverables entity entity.
 *
 * @ingroup rotary_events_main
 *
 * @ContentEntityType(
 *   id = "user_deliverables_entity",
 *   label = @Translation("User deliverables entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\rotary_events_main\UserDeliverablesEntityListBuilder",
 *     "views_data" = "Drupal\rotary_events_main\Entity\UserDeliverablesEntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\rotary_events_main\Form\UserDeliverablesEntityForm",
 *       "add" = "Drupal\rotary_events_main\Form\UserDeliverablesEntityForm",
 *       "edit" = "Drupal\rotary_events_main\Form\UserDeliverablesEntityForm",
 *       "delete" = "Drupal\rotary_events_main\Form\UserDeliverablesEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\rotary_events_main\UserDeliverablesEntityHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\rotary_events_main\UserDeliverablesEntityAccessControlHandler",
 *   },
 *   base_table = "user_deliverables_entity",
 *   translatable = FALSE,
 *   admin_permission = "administer user deliverables entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/user_deliverables_entity/{user_deliverables_entity}",
 *     "add-form" = "/admin/structure/user_deliverables_entity/add",
 *     "edit-form" = "/admin/structure/user_deliverables_entity/{user_deliverables_entity}/edit",
 *     "delete-form" = "/admin/structure/user_deliverables_entity/{user_deliverables_entity}/delete",
 *     "collection" = "/admin/structure/user_deliverables_entity",
 *   },
 *   field_ui_base_route = "user_deliverables_entity.settings"
 * )
 */
class UserDeliverablesEntity extends ContentEntityBase implements UserDeliverablesEntityInterface
{

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values)
  {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName()
  {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name)
  {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime()
  {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp)
  {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner()
  {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId()
  {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid)
  {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account)
  {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
  {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the User deliverables entity entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the User deliverables entity entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

      $fields['registrant_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Registered User Id'))
      ->setDescription(t('The user ID of person whom this deliverable belongs to.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['deliverable'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Deliverable'))
      ->setDescription(t('Item Deliverable.'))
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default:node')
      ->setSetting('handler_settings', [
        'target_bundles' => ['deliverable' => 'deliverable'],
        'auto_create' => FALSE,
      ])
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'visible',
        'type' => 'string',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('view', FALSE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['is_scanned'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Deliverable Scanned'))
      ->setDescription(t('A boolean indicating is deliverable scanned.'))
      ->setDefaultValue(FALSE)
      ->setSettings(['on_label' => 'Scanned', 'off_label' => 'Not Scanned'])
      ->setDisplayOptions('view', [
        'label' => 'visible',
        'type' => 'boolean',
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['scanned_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Scanned Date'))
      ->setDescription(t('Date of first scan.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'datetime_default',
        'settings' => [
          'format_type' => 'medium',
        ],
        'weight' => -9,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => -9,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['zone'] = BaseFieldDefinition::create('entity_reference')
    ->setLabel(t('Zone'))
      ->setDescription(t('Registrant Zone.'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setSetting('handler_settings', ['target_bundles' => ['zones' => 'zones']]);

    $fields['club'] = BaseFieldDefinition::create('entity_reference')
    ->setLabel(t('Club'))
    ->setDescription(t('Registrant Club.'))
    ->setSetting('target_type', 'taxonomy_term')
    ->setDisplayOptions('form', [
      'type' => 'entity_reference_autocomplete',
      'weight' => 5,
      'settings' => [
        'match_operator' => 'CONTAINS',
        'size' => '60',
        'autocomplete_type' => 'tags',
        'placeholder' => '',
      ],
      ])
    ->setSetting('handler_settings', ['target_bundles' => ['club' => 'club']]);

    $fields['status']->setDescription(t('A boolean indicating whether the User deliverables entity is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['ischeckedin'] = BaseFieldDefinition::create('string')
    ->setLabel(t('Is User Checked In'))
     ->setDescription(t('A value indicating whether the user is checked in/checked out.'))
     ->setDefaultValue(null);


    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));


    $fields['checkindata'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Check In Data'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }
}
