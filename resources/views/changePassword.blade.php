@extends(Config::get('oxygen/core::layout'))

@section('content')

<?php

    use Oxygen\Core\Form\FieldMetadata as FieldMeta;
use Oxygen\Core\Html\Form\Label;use Oxygen\Core\Html\Form\Row;use Oxygen\Core\Html\Header\Header;
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

        $fields = [
            new FieldMeta('oldPassword', FieldMeta::TYPE_PASSWORD, true),
            new FieldMeta('password', FieldMeta::TYPE_PASSWORD, true),
            new FieldMeta('passwordConfirmation', FieldMeta::TYPE_PASSWORD, true)
        ];

        foreach($fields as $field) {
            if(!$field->editable) {
                continue;
            }
            $field = new EditableField($field);
            $label = new Label($field->getMeta());
            $row = new Row([$label, $field]);
            echo $row->render();
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
