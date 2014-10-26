@extends(Config::get('oxygen/core::layout'))

@section('content')

<?php

    use Oxygen\Core\Form\Field as FieldMeta;
    use Oxygen\Core\Html\Header\Header;
    use Oxygen\Core\Html\Form\EditableField;
    use Oxygen\Core\Html\Form\Footer;

    $header = Header::fromBlueprint(
        $blueprint,
        Lang::get('oxygen/auth::ui.update.title')
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
        echo Form::model(
            $user,
            [
                'route' => $blueprint->getRouteName('putUpdate'),
                'method' => 'PUT',
                'class' => 'Form--sendAjax Form--warnBeforeExit Form--submitOnKeydown'
            ]
        );

        foreach($blueprint->getFields() as $field) {
            $field = EditableField::fromModel($field, $user);
            echo $field->render();
        }

        $footer = new Footer([
            [
                'route' => $blueprint->getRouteName('getInfo'),
                'label' => Lang::get('oxygen/auth::ui.update.close')
            ],
            [
                'type' => 'submit',
                'label' => Lang::get('oxygen/auth::ui.update.save')
            ]
        ]);

        echo $footer->render();

        echo Form::close();
    ?>
</div>

@stop
