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
            @lang('oxygen/auth::ui.logout.title')
        </h2>
    </div>

    <div class="Row--visual">
        <a href="{{{ URL::route($blueprint->getRouteName('getLogin')) }}}" class="Button Button-color--blue">
            @lang('oxygen/auth::ui.logout.loginAgain')
        </a>
        <a href="{{{ URL::route(Config::get('oxygen/auth::home')) }}}" class="Button Button-color--grey">
            @lang('oxygen/auth::ui.logout.toHome')
        </a>
    </div>
</div>

@stop
