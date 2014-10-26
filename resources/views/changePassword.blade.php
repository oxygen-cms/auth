@extends(Config::get('oxygen/core::layout'))

@section('content')

<?php

    use Oxygen\Core\Form\Field as FieldMeta;
    use Oxygen\Core\Html\Header\Header;
    use Oxygen\Core\Html\Form\EditableField;
    use Oxygen\Core\Html\Form\Footer;

    $header = Header::fromBlueprint(
        $blueprint,
        Lang::get('oxygen/auth::ui.changePassword.title')
    );

    $header->setBackLink(URL::route($blueprint->getRouteName('getInfo')));

?>

<!-- =====================
            HEADER
     ===================== -->

<div class="Block">
    {{ $header->render() }}
</div>

<!-- =====================
            FORM
     ===================== -->

<div class="Block">
    <?php
        echo Form::open([
            'route' => $blueprint->getRouteName('postChangePassword'),
            'class' => 'Form--sendAjax Form--warnBeforeExit Form--submitOnKeydown'
        ]);

        $oldPassword = new FieldMeta('old_password', FieldMeta::TYPE_PASSWORD, true);
        $newPassword = new FieldMeta('password', FieldMeta::TYPE_PASSWORD, true);
        $newPasswordConfirmation = new FieldMeta('password_confirmation', FieldMeta::TYPE_PASSWORD, true);

        $fields = [
            new EditableField($oldPassword),
            new EditableField($newPassword),
            new EditableField($newPasswordConfirmation)
        ];

        foreach($fields as $field) {
            echo $field->render();
        }

        $footer = new Footer([
            [
                'route' => $blueprint->getRouteName('getInfo'),
                'label' => Lang::get('oxygen/auth::ui.changePassword.close')
            ],
            [
                'type' => 'submit',
                'label' => Lang::get('oxygen/auth::ui.changePassword.save')
            ]
        ]);

        echo $footer->render();

        echo Form::close();
    ?>
</div>

@stop
