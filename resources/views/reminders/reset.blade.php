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
            @lang('oxygen/auth::ui.reset.title')
        </h2>
    </div>

    {{ Form::open(array('route' => $blueprint->getRouteName('postReset'), 'class' => 'Form--sendAjax Form--compact')) }}

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="Row--visual">
            {{ Form::text('email', null, [
                'placeholder'   => 'Email',
                'class'         => 'Form-input--fullWidth Form-input--transparent'
            ]) }}
        </div>

        <div class="Row--visual">
            {{ Form::password('password', [
                'autocomplete'  => 'off',
                'placeholder'   => 'Password',
                'class'         => 'Form-input--fullWidth Form-input--transparent'
            ]) }}
        </div>

        <div class="Row--visual">
            {{ Form::password('password_confirmation', [
                'autocomplete'  => 'off',
                'placeholder'   => 'Password Again',
                'class'         => 'Form-input--fullWidth Form-input--transparent'
            ]) }}
        </div>

        <div class="Row Form-footer">
            <button type="submit" class="Button Button-color--blue Button--stretch">
                {{{ Lang::get('oxygen/auth::ui.reset.submit') }}}
            </button>
        </div>

    {{ Form::close() }}

</div>

@stop
