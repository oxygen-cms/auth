@extends(Config::get('oxygen/core::layout'))

<?php
    $bodyClasses = [ 'Login-theme--' . Config::get('oxygen/auth::theme') ];
    $usePage = false;
?>

@section('content')

<div class="Login-background Login-background--sharp"></div>

<!-- =====================
            Logout
     ===================== -->

<div class="Block Block--mini Block--transparent Block--centered">

    <div class="Header Header--noBorder">
        <h2 class="Header-title heading-beta flex-item">
            @lang('oxygen/auth::ui.remind.title')
        </h2>
    </div>

    {{ Form::open(array('route' => $blueprint->getRouteName('postRemind'), 'class' => 'Form--sendAjax Form--compact')) }}

        <div class="Row--visual">
            {{ Form::text('email', null, [
                'placeholder'   => 'Email',
                'class'         => 'Form-input--fullWidth Form-input--transparent'
            ]) }}
        </div>

        <div class="Row Form-footer">
            <button type="submit" class="Button Button-color--blue Button--stretch">
                {{{ Lang::get('oxygen/auth::ui.remind.submit') }}}
            </button>
        </div>

        <div class="Row--visual">
            <a href="{{{ URL::route(Blueprint::get('Auth')->getRouteName('getLogin')) }}}">
                @lang('oxygen/auth::ui.remind.backToLogin')
            </a>
        </div>

    {{ Form::close() }}

</div>

@stop
