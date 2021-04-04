<?php

namespace Drupal\rotary_api\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "event_ack_resource",
 *   label = @Translation("Event ack resource"),
 *   uri_paths = {
 *     "create" = "/api/event/ack"
 *   }
 * )
 */
class EventAckResource extends ResourceBase
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

        // You must to implement the logic of your REST Resource here.
        // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('access content')) {
            throw new AccessDeniedHttpException();
        }

        $entityType = "payment_acknowledgement";
        $bundleName = "default";

        $acknowlegement = entity_create($entityType, array('type' => $bundleName));
        $acknowlegement->set("field_image_upload", $payload["fid"]);
        $acknowlegement->set("field_reference_number", $payload["referenceId"]);
        $acknowlegement->save();
        $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
        $user->set("field_payment_status", "Registered");
        $user->save();
        return new ModifiedResourceResponse($acknowlegement, 200);
    }

}

// 648
