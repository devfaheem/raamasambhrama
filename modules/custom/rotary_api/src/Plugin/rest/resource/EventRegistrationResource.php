<?php

namespace Drupal\rotary_api\Plugin\rest\resource;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "event_registration_resource",
 *   label = @Translation("Event registration resource"),
 *   uri_paths = {
 *     "create" = "/event/registration"
 *   }
 * )
 */
class EventRegistrationResource extends ResourceBase
{

    /**
     * A current user instance.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
        $instance->logger = $container->get('logger.factory')->get('rotary_api');
        $instance->currentUser = $container->get('current_user');
        return $instance;
    }

    /**
     * Responds to POST requests.
     *
     * @param string $payload
     *
     * @return \Drupal\rest\ModifiedResourceResponse
     *   The HTTP response object.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *   Throws exception expected.
     */
    public function post($payload)
    {

        $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $user = \Drupal\user\Entity\User::create();
        $user->setPassword($payload["password"]);
        $user->setEmail($payload["email"]);
        $user->setUsername($payload["email"]);
        $user->addRole("registrant");
        $user->set("field_registration_type", $payload["registrationType"]);
        $user->set("field_registrant_name", $payload["registrantName"]);
        $user->set("field_current_designation", $payload["currentDesignation"]);
        $user->set("field_mobile", $payload["mobile"]);
        $user->set("field_zone", $payload["zoneId"]);
        $user->set("field_club", $payload["clubId"]);
        $user->set("field_contact_address", $payload["contactAddress"]);
        $user->set("field_payment_mode", $payload["paymentMode"]);
        $user->activate();

        $user->field_dependants = $this->getDependants($payload);
        $result = [];
        try {
            $result = $user->save();
            return new ModifiedResourceResponse($user, 200);
        } catch (\Drupal\Core\Entity\EntityStorageException $exception) {
            return new ModifiedResourceResponse(["error" => $exception->getMessage()], 422);
            // return
        }

    }

    public function getDependants($payload)
    {
        $dependants = [];
        foreach ($payload["dependants"] as $dependant) {
            $paragraph = Paragraph::create([
                'type' => 'dependants',
                'field_food_preferences' => array(
                    "value" => $dependant["foodprefs"],
                ),
                'field_mobile' => array(
                    "value" => $dependant["mobilenumber"],
                ),
                'field_name' => array(
                    "value" => $dependant["fullname"],
                ),
            ]);
            $paragraph->save();
            $dependants[] = [
                'target_id' => $paragraph->id(),
                'target_revision_id' => $paragraph->getRevisionId(),
            ];
        }
        return $dependants;
    }

}
