<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="tw-mb-6">
          <div class="tw-mb-3">
            <h4 class="tw-my-0 tw-font-bold tw-text-xl">Clients</h4>
            <a href="#">Customer Contacts &rarr;</a>
          </div>

          <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-3 lg:tw-grid-cols-6 tw-gap-2">
            <div class="tw-border tw-rounded-lg tw-px-4 tw-py-3 tw-bg-white">100 Total Customers</div>
            <div class="tw-border tw-rounded-lg tw-px-4 tw-py-3 tw-bg-white text-success">75 Active</div>
            <div class="tw-border tw-rounded-lg tw-px-4 tw-py-3 tw-bg-white text-danger">25 Inactive</div>
            <div class="tw-border tw-rounded-lg tw-px-4 tw-py-3 tw-bg-white text-info">50 Active Contacts</div>
            <div class="tw-border tw-rounded-lg tw-px-4 tw-py-3 tw-bg-white text-danger">20 Inactive Contacts</div>
            <div class="tw-border tw-rounded-lg tw-px-4 tw-py-3 tw-bg-white">
              <span class="text-muted" data-toggle="tooltip" title="Logged In Today">
                10 Logged In Today
              </span>
            </div>
          </div>
        </div>

        <div class="tw-flex tw-justify-between tw-items-center tw-gap-x-6">
          <div class="tw-flex tw-gap-x-1">
            <a href="<?= admin_url('patient/add_patient'); ?>" class="btn btn-primary">
              <i class="fa-regular fa-plus tw-mr-1"></i> New Patient
            </a>
            <a href="#" class="btn btn-default">
              <i class="fa-solid fa-upload tw-mr-1"></i> Import Customers
            </a>
          </div>
          <div class="tw-inline">
            <div id="vueApp">
              <!-- App Filters Component Placeholder -->
              <div class="tw-text-sm text-muted">Filters Component</div>
            </div>
          </div>
        </div>

        <div class="panel_s tw-mt-2">
          <div class="panel-body">
            <a href="#" class="bulk-actions-btn table-btn">Bulk Actions</a>

            <div class="modal fade" id="customers_bulk_action" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title">Bulk Actions</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                  </div>
                  <div class="modal-body">
                    <div class="checkbox checkbox-danger">
                      <input type="checkbox" id="mass_delete">
                      <label for="mass_delete">Mass Delete</label>
                    </div>
                    <hr />
                    <div>
                      <select multiple class="form-control">
                        <option>Group A</option>
                        <option>Group B</option>
                      </select>
                      <p class="text-danger">Warning: Bulk group changes</p>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal">Close</button>
                    <a href="#" class="btn btn-primary">Confirm</a>
                  </div>
                </div>
              </div>
            </div>

            <div class="panel-table-full">
             <!-- Add this inside your content area -->
<table id="patients-table" class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Company</th>
            <th>Primary Contact</th>
            <th>Primary Email</th>
            <th>Phone</th>
            <th>Active</th>
            <th>Groups</th>
            <th>Date Created</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Testing</td>
            <td>cd g</td>
            <td>ffdfd@ffd.com</td>
            <td>1234567890</td>
            <td><input type="checkbox" checked /></td>
            <td>Group A</td>
            <td>2025-03-28 14:33:29</td>
        </tr>
        <tr>
            <td>2</td>
            <td>New</td>
            <td>John Doe</td>
            <td>john@doe.com</td>
            <td>9876543210</td>
            <td><input type="checkbox" checked /></td>
            <td>Group B</td>
            <td>2025-04-11 17:23:02</td>
        </tr>
    </tbody>
</table>

            </div>

          </div>
        </div>

      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script>
  $(function() {
    console.log('Table initialized');
  });

  function customers_bulk_action(event) {
    alert('Bulk action triggered');
  }
</script>
</body>
</html>
