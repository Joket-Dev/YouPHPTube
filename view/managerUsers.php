<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=".__("You can not manager users"));
    exit;
}
require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("Users"); ?></title>
        <?php
        include $global['systemRootPath'].'view/include/head.php';
        ?>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>

        <div class="container">

            <button type="button" class="btn btn-default" id="addUserBtn">
                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?php echo __("New User"); ?>
            </button>

            <table id="grid" class="table table-condensed table-hover table-striped">
                <thead>
                    <tr>
                        <th data-column-id="id" data-type="numeric" data-identifier="true"><?php echo __("ID"); ?></th>
                        <th data-column-id="user"><?php echo __("User"); ?></th>
                        <th data-column-id="name" data-order="desc"><?php echo __("Name"); ?></th>
                        <th data-column-id="email" ><?php echo __("E-mail"); ?></th>
                        <th data-column-id="created" ><?php echo __("Created"); ?></th>
                        <th data-column-id="modified" ><?php echo __("Modified"); ?></th>
                        <th data-column-id="isAdmin" ><?php echo __("Is Admin"); ?></th>
                        <th data-column-id="status" ><?php echo __("Status"); ?></th>
                        <th data-column-id="commands" data-formatter="commands" data-sortable="false"></th>
                    </tr>
                </thead>
            </table>

            <div id="userFormModal" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><?php echo __("User Form"); ?></h4>
                        </div>
                        <div class="modal-body">
                            <form class="form-compact"  id="updateUserForm" onsubmit="">
                                <input type="hidden" id="inputUserId"  >
                                <label for="inputUser" class="sr-only"><?php echo __("User"); ?></label>
                                <input type="text" id="inputUser" class="form-control first" placeholder="<?php echo __("User"); ?>" autofocus required="required">
                                <label for="inputPassword" class="sr-only"><?php echo __("Password"); ?></label>
                                <input type="password" id="inputPassword" class="form-control" placeholder="<?php echo __("Password"); ?>" required="required">
                                <label for="inputEmail" class="sr-only"><?php echo __("E-mail"); ?></label>
                                <input type="email" id="inputEmail" class="form-control" placeholder="<?php echo __("E-mail"); ?>" >
                                <label for="inputName" class="sr-only"><?php echo __("Name"); ?></label>
                                <input type="text" id="inputName" class="form-control last" placeholder="<?php echo __("Name"); ?>" >
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" value="isAdmin" id="isAdmin"> <?php echo __("is Admin"); ?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" value="status" id="status"> <?php echo __("is Active"); ?>
                                    </label>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Close"); ?></button>
                            <button type="button" class="btn btn-primary" id="saveUserBtn"><?php echo __("Save changes"); ?></button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->

        </div><!--/.container-->

            <?php
            include 'include/footer.php';
            ?>

        <script>
            $(document).ready(function () {



                var grid = $("#grid").bootgrid({
                    ajax: true,
                    url: "<?php echo $global['webSiteRootURL'] . "users.json"; ?>",
                    formatters: {
                        "commands": function (column, row)
                        {
                            var editBtn = '<button type="button" class="btn btn-xs btn-default command-edit" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="Edit"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>'
                            //var deleteBtn = '<button type="button" class="btn btn-default btn-xs command-delete"  data-row-id="' + row.id + '  data-toggle="tooltip" data-placement="left" title="Delete""><span class="glyphicon glyphicon-erase" aria-hidden="true"></span></button>';
                            //return editBtn + deleteBtn;
                            return editBtn;
                        }
                    }
                }).on("loaded.rs.jquery.bootgrid", function ()
                {
                    /* Executes after data is loaded and rendered */
                    grid.find(".command-edit").on("click", function (e) {
                        var row_index = $(this).closest('tr').index();
                        var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                        console.log(row);

                        $('#inputUserId').val(row.id);
                        $('#inputUser').val(row.user);
                        $('#inputPassword').val('');
                        $('#inputEmail').val(row.email);
                        $('#inputName').val(row.name);
                        $('#isAdmin').prop('checked', (row.isAdmin === "1" ? true : false));
                        $('#status').prop('checked', (row.status === "a" ? true : false));

                        $('#userFormModal').modal();
                    }).end().find(".command-delete").on("click", function (e) {
                        /*
                        var row_index = $(this).closest('tr').index();
                        var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                        console.log(row);
                        swal({
                            title: "<?php echo __("Are you sure?"); ?>",
                            text: "<?php echo __("You will not be able to recover this user!"); ?>",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "<?php echo __("Yes, delete it!"); ?>",
                            closeOnConfirm: false
                        },
                                function () {

                                    modal.showPleaseWait();
                                    $.ajax({
                                        url: 'deleteUser',
                                        data: {"id": row.id},
                                        type: 'post',
                                        success: function (response) {
                                            if (response.status === "1") {
                                                $("#grid").bootgrid("reload");
                                                swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your user has been deleted!"); ?>", "success");
                                            } else {
                                                swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your user has NOT been deleted!"); ?>", "error");
                                            }
                                            modal.hidePleaseWait();
                                        }
                                    });
                                });
                                */
                    });
                });



                $('#addUserBtn').click(function (evt) {
                    $('#inputUserId').val('');
                    $('#inputUser').val('');
                    $('#inputPassword').val('');
                    $('#inputEmail').val('');
                    $('#inputName').val('');
                    $('#isAdmin').prop('checked', false);
                    $('#status').prop('checked', true);

                    $('#userFormModal').modal();
                });

                $('#saveUserBtn').click(function (evt) {
                    $('#updateUserForm').submit();
                });

                $('#updateUserForm').submit(function (evt) {
                    evt.preventDefault();
                    modal.showPleaseWait();
                    $.ajax({
                        url: 'addNewUser',
                        data: {
                            "id": $('#inputUserId').val(), 
                            "user": $('#inputUser').val(), 
                            "pass": $('#inputPassword').val(), 
                            "email": $('#inputEmail').val(), 
                            "name": $('#inputName').val(), 
                            "isAdmin": $('#isAdmin').is(':checked'), 
                            "status": $('#status').is(':checked')?'a':'i'
                        },
                        type: 'post',
                        success: function (response) {
                            if (response.status > "0") {
                                $('#userFormModal').modal('hide');
                                $("#grid").bootgrid("reload");
                                swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your user has been saved!"); ?>", "success");
                            } else {
                                swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your user has NOT been saved!"); ?>", "error");
                            }
                            modal.hidePleaseWait();
                        }
                    });
                    return false;
                });
            });

        </script>
    </body>
</html>
