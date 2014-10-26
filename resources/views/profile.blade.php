@extends(Config::get('oxygen/core::layout'))

@section('content')

<?php

    use Oxygen\Core\Action\Action;
    use Oxygen\Core\Html\Header\Header;
    use Oxygen\Core\Html\Form\StaticField;
    use Oxygen\Core\Html\Toolbar\ButtonToolbarItem;

    $header = Header::fromBlueprint(
        $blueprint,
        Lang::get('oxygen/auth::ui.profile.title')
    );

?>

<!-- =====================
            HEADER
     ===================== -->

<div class="Block">
    {{ $header->render() }}
</div>

<!-- =====================
          BASIC INFO
     ===================== -->

<div class="Block">
    <div class="Row">
        <h2 class="heading-gamma">Basic Information</h2>
        <?php
            $toolbarItem = $blueprint->getToolbarItem('getUpdate');
            $toolbarItem->label = 'Edit';
            if($toolbarItem->shouldRender()) {
                echo $toolbarItem->render(['margin' => 'horizontal']);
            }
        ?>
    </div>
    <?php
        foreach($blueprint->getFields() as $field) {
            $field = StaticField::fromModel($field, $user, true);
            echo $field->render();
        }
    ?>
</div>

<!-- =====================
        CHANGE PASSWORD
     ===================== -->

<?php
    $toolbarItem = $blueprint->getToolbarItem('getChangePassword');
    $toolbarItem->label = 'Change your password now';
    $toolbarItem->color = 'blue';
?>

@if($toolbarItem->shouldRender())

<div class="Block">
    <div class="Row">
        <h2 class="heading-gamma">Change Password</h2>
    </div>
    <div class="Row">
        <div class="Form-content flex-item">
            <p>
                Choosing a strong password will help keep your account safe.<br>
                Try to use as many different characters, numbers and symbols as you possibly can, and make sure you don't use the password anywhere else.
            </p>
            <br>
            <?php
                echo $toolbarItem->render();
            ?>
        </div>
    </div>
</div>

@endif

<!-- =====================
       TERMINATE ACCOUNT
     ===================== -->

<?php
    $toolbarItem = $blueprint->getToolbarItem('deleteForce');
?>

@if($toolbarItem->shouldRender())

<div class="Block">
    <div class="Row">
        <h2 class="heading-gamma">Terminate Account</h2>
    </div>
    <div class="Row">
        <div class="Form-content flex-item">
            <p>If you are sure you delete <strong>your entire account and everything associated with it</strong>, then click the button below.</p>
            <br>
            <?php
                echo $toolbarItem->render();
            ?>
        </div>
    </div>
</div>

@endif

@stop
