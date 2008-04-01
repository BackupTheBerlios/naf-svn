<?php

/**
 * Common settings - shared between all installations of the application
 */

$commonSettings = array();

/**
 * now let's merge settings for a concrete installation - with the common;
 * NOTE: concrete settings override common!
 */
$settings = array_merge($commonSettings, $settings);