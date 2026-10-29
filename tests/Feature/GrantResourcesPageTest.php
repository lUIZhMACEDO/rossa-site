<?php

it('renders the statement copy script on the grant resources page', function () {
    $response = $this->get(route('grant-resources'));

    $response->assertSee('navigator.clipboard.writeText', false);
});
