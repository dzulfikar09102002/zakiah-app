<?php

test('beranda mengarahkan ke dashboard', function () {
    $this->get(route('home'))->assertRedirect('/dashboard');
});
