<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<script type="text/javascript">
    $(document).ready(function() {
        $('#UTable').DataTable({
            "dom": '<"row"r>t<"row"<"col-md-6"i><"col-md-6"p>><"clear">',
            "order": [[ 0, "desc" ]],
            "pageLength": Settings.rows_per_page,
            "processing": false, "serverSide": false,
            "buttons": []
        });
    });
</script>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h4><?= $page_title; ?></h4>
                    <div class="pull-right">

                        <a href="<?= site_url('users/add'); ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> <?= lang('add_user'); ?>
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <table id="UTable" class="table table-bordered table-striped table-hover">
                        <thead class="cf">
                        <tr>
                            <th><?php echo lang('first_name'); ?></th>
                            <th><?php echo lang('last_name'); ?></th>
                            <th><?php echo lang('email'); ?></th>
                            <th><?php echo lang('group'); ?></th>
                            <th><?php echo lang('store'); ?></th>
                            <th style="width:100px;"><?php echo lang('status'); ?></th>
                            <th style="width:80px;"><?php echo lang('actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($users as $user) {
                            echo '<tr>';
                            echo '<td>' . $user->first_name . '</td>';
                            echo '<td>' . $user->last_name . '</td>';
                            echo '<td>' . $user->email . '</td>';
                            echo '<td>' . $user->group . '</td>';
                            echo '<td>' . $user->store . '</td>';
                            echo '<td class="text-center" style="padding:6px;">' . ($user->active ? '<span class="label label-success">' . lang('active') . '</span' : '<span class="label label-danger">' . lang('inactive') . '</span>') . '</td>';
                            echo '<td class="text-center" style="padding:6px;">
    <div class="btn-group">
        <button type="button" class="btn btn-primary  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-cog"></i> ' . lang("actions") . ' <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-right">
            <li>
                <a class="tip" title="' . lang("profile") . '" href="' . site_url('users/profile/' . $user->id) . '">
                    <i class="fa fa-edit"></i> ' . lang("profile") . '
                </a>
            </li>
            <li>
                <a class="tip text-danger" title="' . lang("delete") . '" href="' . site_url('auth/delete/' . $user->id) . '" onclick="return confirm(\'' . lang('alert_x_user') . '\')">
                    <i class="fa-solid fa-trash"></i> ' . lang("delete") . '
                </a>
            </li>
        </ul>
    </div>
</td>';
                            echo '</tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
