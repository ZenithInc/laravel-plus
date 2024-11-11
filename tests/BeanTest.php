<?php /** @noinspection PhpUndefinedMethodInspection */


use Zenith\LaravelPlus\Attributes\BeanList;
use Zenith\LaravelPlus\Bean;
use Zenith\LaravelPlus\Exceptions\PropertyNotFoundException;

it('case 1', function () {
    $testBean = new class(['username' => 'bob']) extends Bean {
        protected string $username;
    };
    expect($testBean->getUsername())->toBe('bob');
});

it('converts to JSON', function () {
    $testBean = new class(['username' => 'bob', 'password' => 'passW0rd']) extends Bean {
        protected string $username;
        protected string $password;
    };
    $arr = json_decode($testBean->toJson(), true);
    expect($arr['username'])->toBe('bob')->and($arr['password'])->toBe('passW0rd');
});

it('initializes with BeanList', function () {
    $wrapper = new Wrapper([
        'items' => [
            ['name' => 'bob'],
            ['name' => 'tom']
        ]
    ]);
    $arr = $wrapper->toArray();
    expect($arr['items'][0]['name'])->toBe('bob')
        ->and($arr['items'][1]['name'])->toBe('tom')
        ->and($wrapper->getItems()[0]->getName())->toBe('bob')
        ->and($wrapper->getItems()[1]->getName())->toBe('tom');
});


it('convert type with class', function () {
    $bean = new Status(['value' => 'VALID', 'page' => '1']);
    expect($bean->getValue())->toBe(TestEnum::VALID)
        ->and($bean->getPage())->toEqual(1);
});

it('test setter and getter method', function () {
    $testBean = new class extends Bean {
        protected string $username;
    };
    $testBean->setUsername('bob');
    expect($testBean->getUsername())->toBe('bob');
});

it('skip property', function () {
    $testBean = new class extends Bean {
        protected array $_skip = ['skip'];
        protected string $skip;
    };
    $arr = $testBean->toArray();
    expect($arr)->toBeEmpty();
});

it('test non-existing property', function () {
    $testBean = new class extends Bean {};
    $testBean->setNotExistsProperty("undefined");
})->throws(PropertyNotFoundException::class);

it('test to array with snake', function () {
    $testBean = new class(['userId' => 1]) extends Bean {
        protected int $userId;
    };
    $arr = $testBean->toArray();
    expect($arr)->toHaveKey('user_id');
    $arr = $testBean->toArray(false);
    expect($arr)->toHaveKey('userId');
});

it('test union type', function () {
    $testBean = new Component([
        'type' => 'text',
        'settings' => ['minLength' => 3, 'maxLength' => 10],
    ]);
    expect($testBean->getSettings()->getMaxLength())->toBe(10)
        ->and($testBean->getSettings()->getMinLength())->toBe(3);
    $testBean = new Component([
        'type' => 'number',
        'settings' => ['minValue' => 3, 'maxValue' => 10],
    ]);
    expect($testBean->getSettings()->getMaxValue())->toBe(10)
        ->and($testBean->getSettings()->getMinValue())->toBe(3);
});

it('test union type to array', function () {
    $testBean = new Component([
        'type' => 'text',
        'settings' => ['minLength' => 3, 'maxLength' => 10],
    ]);
    $arr = $testBean->toArray();
    expect($arr['settings']['min_length'])->toBe(3)
        ->and($arr['settings']['max_length'])->toBe(10);
});