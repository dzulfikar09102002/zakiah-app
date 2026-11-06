<?php

// Registrasi publik dimatikan; akun dibuat lewat menu Karyawan.
test('registration screen is not available', function () {
    $this->get('/register')->assertNotFound();
});

test('users cannot register themselves', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
});
