<?php

namespace Drupal\rotary_events_main\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class EventPagesController.
 */
class EventPagesController extends ControllerBase
{
    public function eventsDay1()
    {
        return   $build = [
        '#theme' => 'programme_day_1'
        ];
    }

    public function eventsDay2()
    {
        return   $build = [
        '#theme' => 'programme_day_2'
        ];
    }

    public function eventsDay3()
    {
        return   $build = [
        '#theme' => 'programme_day_3'
        ];
    }
}