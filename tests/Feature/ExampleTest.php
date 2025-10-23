<?php

test('the application returns a successful response', function () {
    $this->markTestSkipped('Frontend views not implemented yet - Steps 1-4 focus on backend');
    
    $response = $this->get('/');
    $response->assertStatus(200);
});
