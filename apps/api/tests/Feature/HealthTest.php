<?php

describe('Health endpoint', function () {
    it('returns 200 on GET /up', function () {
        $this->get('/up')->assertStatus(200);
    });

    it('returns 404 on unknown route', function () {
        $this->get('/unknown-route')->assertStatus(404);
    });
});
