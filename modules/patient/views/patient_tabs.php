<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<select class="tw-block md:tw-hidden form-control" onchange="redirectToTab(this)">
  <?php foreach ($patient_tabs as $slug => $tab): ?>
    <option value="<?= admin_url('patient/' . $slug); ?>">
      <?= $tab['name']; ?>
    </option>
  <?php endforeach; ?>
</select>

<nav class="customer-tabs tw-hidden tw-flex-1 tw-flex-col md:tw-flex" aria-label="Sidebar">
  <ul role="list" class="tw-space-y-0.5">
    <?php foreach ($patient_tabs as $slug => $tab): ?>
      <li class="customer_tab_<?= $slug; ?>">
        <a href="<?= $tab['href'] !== '#' ? $tab['href'] : admin_url('patient/' . $slug); ?>"
          class="tw-group tw-flex tw-items-center tw-gap-x-3 tw-rounded-md tw-p-2 tw-font-medium
            tw-text-neutral-800 hover:tw-bg-neutral-50 hover:tw-text-primary-600">

          <?php if (!empty($tab['icon'])): ?>
            <i class="<?= $tab['icon']; ?> fa-lg fa-fw tw-shrink-0"></i>
          <?php endif; ?>

          <span><?= $tab['name']; ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>
