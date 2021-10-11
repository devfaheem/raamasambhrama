<?php

namespace Drupal\rotary_events_main\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Class RegistrationApprovalForm.
 */
class RegistrationApprovalForm extends FormBase
{

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'registration_approval_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, AccountInterface $user = null)
    {
        $depandantIds = $user->field_dependants->getValue();
        $depandants = [];
        foreach ($depandantIds as $id) {
            $depandants[] = \Drupal\paragraphs\Entity\Paragraph::load($id['target_id']);
        }

        $query = \Drupal::entityQuery('payment_acknowledgement')
            ->condition('type', 'default')
            ->condition('uid', $user->get("uid")->value);

        $acknowledgement = $query->execute();
        $acknowledgement = \Drupal::entityTypeManager()->getStorage("payment_acknowledgement")->load(current($acknowledgement));

        $form["#theme"] = "registration_approval";
        $form['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Approve'),
            '#attributes' => array('class' => array('float-right')),
        ];
        $form['userid'] = [
            '#type' => 'hidden',
            '#value' => $user->get("uid")->value,
            '#attributes' => array('class' => array('float-right')),
        ];
        $form["#registrant"] = $user;
        $form["#decendants"] = $depandants;
        $form["#acknowledgement"] = $acknowledgement;

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        foreach ($form_state->getValues() as $key => $value) {
            // @TODO: Validate fields.
        }
        // if (!is_numeric($title[0]['value'])) {
        //     $form_state->setErrorByName('title', t('Your title should be number'));
        // }
        parent::validateForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $userid = $form_state->getValue("userid");
        $user = \Drupal\user\Entity\User::load($userid);
        $user->set("field_payment_status", "ConfirmedRegistration");
        $user->save();
        // Display result.
        \Drupal::messenger()->addMessage("Approval Performed");

    }

}
