@extends(Config::get('oxygen/core::layout'))

@section('content')

<?php

use Oxygen\Core\Html\Form\Label;use Oxygen\Core\Html\Form\Row;use Oxygen\Core\Html\Header\Header;
    use Oxygen\Core\Html\Form\EditableField;
    use Oxygen\Core\Html\Form\Footer;use Oxygen\Core\Html\Toolbar\ButtonToolbarItem;use Oxygen\Core\Html\Toolbar\SubmitToolbarItem;

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
            if(!$field->editable) {
                continue;
            }
            $field = EditableField::fromEntity($field, $user);
            $label = new Label($field->getMeta());
            $row = new Row([$label, $field]);
            echo $row->render();
        }

        $footer = new Row([
            new ButtonToolbarItem(Lang::get('oxygen/auth::ui.update.close'), $blueprint->getAction('getInfo')),
            new SubmitToolbarItem(Lang::get('oxygen/auth::ui.update.save'))
        ]);
        $footer->isFooter = true;

        echo $footer->render();

        echo Form::close();
    ?>
</div>

@stop
