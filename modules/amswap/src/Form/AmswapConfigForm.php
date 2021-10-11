<?php

namespace Drupal\amswap\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AmswapConfigForm.
 *
 * @package Drupal\amswap\Form
 */
class AmswapConfigForm extends ConfigFormBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'amswap.amswapconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'amswap_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('amswap.amswapconfig');

    // Prepare role selector options.
    $roles = $this->entityTypeManager
      ->getStorage('user_role')
      ->loadMultiple();

    // Add the first instructional option.
    $role_options = ['' => $this->t('- Select a role -')];
    foreach ($roles as $role) {
      $role_options[$role->id()] = $role->label();
    }

    // Prepare menu selector options.
    $menus = $this->entityTypeManager->getStorage('menu')->loadMultiple();
    // Add the first instructional option.
    $menu_options = ['' => $this->t('- Select a menu -')];
    foreach ($menus as $menu) {
      $menu_options[$menu->id()] = $menu->label();
    }

    $role_menu_pairs = $config->get('role_menu_pairs');
    $num_pairs = $form_state->get('num_pairs');
    $num_pairs = $num_pairs ? $num_pairs : 1;

    // Use the larger of pairs saved or pairs added using the button.
    $num_pairs_in_form = count($role_menu_pairs) > $num_pairs ? count($role_menu_pairs) : $num_pairs;
    $form_state->set('num_pairs', $num_pairs_in_form);

    for ($i = 0; $i < $num_pairs_in_form; $i++) {
      $form['role_menu_pairs'][$i] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Role-Menu Pair @i', [
          '@i' => ($i + 1),
        ]),
      ];
      $role = isset($role_menu_pairs[$i]) ? $role_menu_pairs[$i]['role'] : NULL;
      $form['role_menu_pairs'][$i]['pair-' . $i . '-role'] = [
        '#type' => 'select',
        '#title' => $this->t('Role'),
        '#description' => $this->t('Select a role.'),
        '#default_value' => $role,
        '#options' => $role_options,
      ];
      $menu = isset($role_menu_pairs[$i]) ? $role_menu_pairs[$i]['menu'] : NULL;
      $form['role_menu_pairs'][$i]['pair-' . $i . '-menu'] = [
        '#type' => 'select',
        '#title' => $this->t('Menu'),
        '#description' => $this->t('Select which menu should be displayed for that role.'),
        '#default_value' => $menu,
        '#options' => $menu_options,
      ];
      $form['role_menu_pairs'][$i]['pair-' . $i . '-delete'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove @i', [
          '@i' => ($i + 1),
        ]),
        '#submit' => ['::deletePair'],
        '#attributes' => ['pair_num' => $i],
      ];
    }

    $form['add_pair'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another role-menu pair'),
      '#submit' => ['::addPair'],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function addPair(array $form, FormStateInterface &$form_state) {
    // Get the current number of pairs, or 1 if not provided.
    $num_pairs = $form_state->get('num_pairs');
    $num_pairs = $num_pairs ? $num_pairs : 1;
    // Add 1 to the number of role-menu pairs that should be displayed.
    $form_state->set('num_pairs', $num_pairs + 1);

    // Set the form to be rebuilt.
    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function deletePair(array $form, FormStateInterface &$form_state) {
    $button = $form_state->getTriggeringElement();
    $item = $button['#attributes']['pair_num'];
    $form_state->unsetValue('pair-' . $item . '-role');
    $form_state->unsetValue('pair-' . $item . '-menu');

    $msg = $this->t('Pair @i removed. Other pairs saved.', [
      '@i' => ($item + 1),
    ]);
    $this->messenger()->addStatus($msg, FALSE);

    $this->submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $this->checkForDuplicates($form, $form_state);
  }

  /**
   * Check for duplicated pairs in the form.
   *
   * @param array $form
   *   Drupal form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Drupal form_state object.
   */
  public function checkForDuplicates(array &$form, FormStateInterface $form_state) {
    // Get trigger.
    $trigger = $form_state->getTriggeringElement();
    // Check if pair is being deleted, if so; skip the validation for that pair.
    $skip = NULL;
    if (strpos($trigger['#id'], 'delete') !== FALSE) {
      $skip = $trigger['#attributes']['pair_num'];
    }

    // Set up variables for the pairs.
    $pairs = [];
    $num_pairs = $form_state->get('num_pairs');
    // Ensure number of pairs is always at least 1.
    $num_pairs = $num_pairs ? $num_pairs : 1;

    // Loop through all pairs to find duplicates.
    for ($i = 0; $i < $num_pairs; $i++) {
      // If skip has been set, skip this item.
      if ($i === $skip) {
        continue;
      }
      $role = $form_state->getValue('pair-' . $i . '-role');
      $menu = $form_state->getValue('pair-' . $i . '-menu');
      // Save first pair for this role.
      if (!array_key_exists($role, $pairs)) {
        $pairs[$role] = [$menu];
      }
      else {
        // If pair already exists; set error message.
        if (in_array($menu, $pairs[$role])) {
          $msg = $this->t('Pair @i is a duplicate.', ['@i' => ($i + 1)]);
          $form_state->setErrorByName('pair-' . $i . '-role', $msg);
          $form_state->setErrorByName('pair-' . $i . '-menu');
        }
        else {
          // Save this combination of role and menu.
          $pairs[$role][] = $menu;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    parent::submitForm($form, $form_state);

    $trigger = $form_state->getTriggeringElement();
    $pairs = [];
    $pair_index = 0;
    $num_pairs = $form_state->get('num_pairs');
    $num_pairs = $num_pairs ? $num_pairs : 1;

    for ($i = 0; $i < $num_pairs; $i++) {
      // $pairs[$] = $i;
      $role = $form_state->getValue('pair-' . $i . '-role');
      $menu = $form_state->getValue('pair-' . $i . '-menu');
      if ($role && $menu) {
        $pairs[$pair_index]['role'] = $role;
        $pairs[$pair_index]['menu'] = $menu;
        $pair_index++;
      }
      // Otherwise if not triggered by a delete button.
      elseif (strpos($trigger['#id'], 'delete') === FALSE) {
        $msg = $this->t('Pair @i was missing either a role or a menu value, so was not saved.', [
          '@i' => ($i + 1),
        ]);
        $this->messenger()->addWarning($msg, FALSE);
      }
    }

    $this->config('amswap.amswapconfig')
      ->set('role_menu_pairs', $pairs)
      ->save();

    $url = Url::fromRoute('system.performance_settings');
    $link = Link::fromTextAndUrl('Clear caches', $url);
    $msg = $link->toString() . ' to see the changes.';
    $rendered_msg = Markup::create($msg);

    $this->messenger()->addStatus($rendered_msg, FALSE);

    // drupal_flush_all_caches();
  }

}
