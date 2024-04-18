<?php

beforeEach(function () {
    $this->data = [
        'username' => 'bob',
        'latest_login_ip' => '127.0.0.1',
        'books' => [
            ['name' => 'Programming PHP:Creating Dynamic Web Pages'],
            ['name' => 'Learning PHP, MySQL,Javascript,CSS && HTML5'],
        ]
    ];
    $this->bean = new SampleBean($this->data);
});

it('initializes with data', function () {
    $arr = $this->bean->toArray();
    foreach ($this->data as $key => $value) {
        expect($arr[$key])->toBe($value);
    }
});

it('converts to JSON', function () {
    $json = json_decode($this->bean->toJson(), true);
    foreach ($this->data as $key => $value) {
        expect($json[$key])->toBe($value);
    }
});

it('initializes with BeanList', function () {
    foreach ($this->bean->books as $key => $book) {
        expect($book->getName())->toBe($this->data['books'][$key]['name']);
    }
});