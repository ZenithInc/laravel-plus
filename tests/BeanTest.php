<?php

use Zenith\LaravelPlus\Exceptions\PropertyNotFoundException;

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
        'pivot' => 'skip_property',
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
    unset($this->verifyDataWithArray['pivot']);
    foreach ($this->verifyDataWithArray as $key => $value) {
        expect($arr[$key])->toBe($value);
    }
});

it ('init empty bean list', function () {
    $arr = (new SampleBean2(['username' => 'bob', 'subs' => []]))->toArray();
    expect($arr)->toBeArray();
});

it('converts to JSON', function () {
    $json = json_decode($this->bean->toJson(), true);
    unset($this->verifyDataWithJson['pivot']);
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

it('test __set method', function () {
    $this->bean->page = 2;
    expect($this->bean->getPage())->toBe(2);
    $arr = $this->bean->toArray();
    expect($arr['page'])->toBe(2);
    $json = json_decode($this->bean->toJson(), true);
    expect($json['page'])->toBe(2);
});

it('test __get method', function () {
    $this->bean->age = 10;
    expect($this->bean->age)->toBe(10);
});

it('skip property', function () {
    $arr = $this->bean->toArray();
    expect(!isset($arr['pivot']))->toBe(true);
});

it('test __call method', function () {
    $this->bean->setAge(10);
    $arr = $this->bean->toArray();
    expect($arr['age'])->toBe(10)
        ->and($this->bean->getAge())->toBe(10);
});

it('test __call method2', function () {
    $this->bean->setUsername('bob')->setAge(10);
    expect($this->bean->getAge())->toBe(10);
});

it ('test non-existing property', function () {
    $this->bean->setNotExistsProperty("undefined");
})->throws(PropertyNotFoundException::class);