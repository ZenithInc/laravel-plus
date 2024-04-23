<?php

beforeEach(function () {
    $this->data = [
        'username' => 'bob',
        'latest_login_ip' => '127.0.0.1',
        'books' => [
            ['name' => 'Programming PHP:Creating Dynamic Web Pages'],
            ['name' => 'Learning PHP, MySQL,Javascript,CSS && HTML5'],
        ],
        'page' => '1',
        'status' => 'VALID',
    ];
    $this->verifyDataWithArray = array_merge($this->data, [
        'page' => 1,
        'status' => TestEnum::VALID,
    ]);
    $this->verifyDataWithJson = array_merge($this->data, [
        'page' => 1,
    ]);
    $this->bean = new SampleBean($this->data);
});

it('initializes with data', function () {
    $arr = $this->bean->toArray();
    foreach ($this->verifyDataWithArray as $key => $value) {
        expect($arr[$key])->toBe($value);
    }
});

it('converts to JSON', function () {
    $json = json_decode($this->bean->toJson(), true);
    foreach ($this->verifyDataWithJson as $key => $value) {
        expect($json[$key])->toBe($value);
    }
});

it('initializes with BeanList', function () {
    foreach ($this->bean->books as $key => $book) {
        expect($book->getName())->toBe($this->data['books'][$key]['name']);
    }
});

it('convert type with function', function () {
    expect($this->bean->getPage())->toBe(1);
});

it('convert type with class', function () {
    expect($this->bean->getStatus())->toBe(TestEnum::VALID);
});

it('setter', function () {
    $this->bean->page = 2;
    expect($this->bean->getPage())->toBe(2);
    $arr = $this->bean->toArray();
    expect($arr['page'])->toBe(2);
    $json = json_decode($this->bean->toJson(), true);
    expect($json['page'])->toBe(2);
});