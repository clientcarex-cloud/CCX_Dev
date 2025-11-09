<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Run on module activation.
 */
function ccx_creator_install(): void
{
    // Nothing to migrate yet; placeholder for future database/schema updates.
}

/**
 * Run on module deactivation.
 */
function ccx_creator_uninstall(): void
{
    // Nothing to roll back; placeholder keeps Perfex hooks happy.
}
