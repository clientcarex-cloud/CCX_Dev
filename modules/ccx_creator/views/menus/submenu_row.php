<?php
defined('BASEPATH') or exit('No direct script access allowed');

$submenuIndex = isset($idx) ? (int) $idx : 0;
$submenuData  = $submenu ?? [];
$statusId     = 'submenu_status_' . $submenuIndex . '_' . uniqid();
$nameValue    = html_escape($submenuData['name'] ?? '');
$iconValue    = html_escape($submenuData['icon'] ?? 'fa-regular fa-circle-dot');
$rolesValue   = $submenuData['role_access'] ?? [];
$isActive     = (int) ($submenuData['status'] ?? 1) === 1;
?>

<div class="panel panel-default ccx-submenu-card" data-index="<?php echo $submenuIndex; ?>">
    <div class="panel-heading tw-flex tw-items-center tw-justify-between tw-gap-2">
        <strong>Sub-Menu <?php echo $submenuIndex + 1; ?></strong>
        <button type="button" class="btn btn-link text-danger ccx-remove-submenu">
            <i class="fa-regular fa-trash-can mright5" aria-hidden="true"></i>
            Delete
        </button>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label class="control-label">Name</label>
                    <input type="text"
                        class="form-control"
                        name="submenus[<?php echo $submenuIndex; ?>][name]"
                        value="<?php echo $nameValue; ?>"
                        placeholder="e.g. Pipeline">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">Icon</label>
                    <input type="text"
                        class="form-control"
                        name="submenus[<?php echo $submenuIndex; ?>][icon]"
                        value="<?php echo $iconValue; ?>"
                        placeholder="fa-regular fa-object-group">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">Access</label>
                    <select name="submenus[<?php echo $submenuIndex; ?>][role_access][]"
                        class="selectpicker"
                        data-width="100%"
                        multiple
                        data-actions-box="true"
                        title="All staff">
                        <?php foreach ($roles as $role) : ?>
                            <?php $selected = in_array((int) $role['roleid'], $rolesValue ?? [], true) ? 'selected' : ''; ?>
                            <option value="<?php echo (int) $role['roleid']; ?>" <?php echo $selected; ?>>
                                <?php echo html_escape($role['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group text-center">
                    <label class="control-label">Status</label>
                    <div class="onoffswitch">
                        <input type="hidden" name="submenus[<?php echo $submenuIndex; ?>][status]" value="0">
                        <input type="checkbox"
                            class="onoffswitch-checkbox"
                            id="<?php echo $statusId; ?>"
                            name="submenus[<?php echo $submenuIndex; ?>][status]"
                            value="1"
                            <?php echo $isActive ? 'checked' : ''; ?>>
                        <label class="onoffswitch-label" for="<?php echo $statusId; ?>"></label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
