<?php

namespace Phpactor\WorseReflection\Tests\Integration\Reflection;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Visibility;
use Phpactor\WorseReflection\Type;

class ReflectionPropertyTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionProperty
     */
    public function testReflectProperty(string $source, string $class, \Closure $assertion)
    {
        $class = $this->createReflector($source)->reflectClass(ClassName::fromString($class));
        $assertion($class->properties());
    }

    public function provideReflectionProperty()
    {
        return [
            'It reflects a property' => [
                <<<'EOT'
<?php

class Foobar
{
    public $property;
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertEquals('property', $properties->get('property')->name());
                    $this->assertInstanceOf(ReflectionProperty::class, $properties->get('property'));
                },
            ],
            'Private visibility' => [
                <<<'EOT'
<?php

class Foobar
{
    private $property;
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertEquals(Visibility::private(), $properties->get('property')->visibility());
                },
            ],
            'Protected visibility' => [
                <<<'EOT'
<?php

class Foobar
{
    protected $property;
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertEquals(Visibility::protected(), $properties->get('property')->visibility());
                },
            ],
            'Public visibility' => [
                <<<'EOT'
<?php

class Foobar
{
    public $property;
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertEquals(Visibility::public(), $properties->get('property')->visibility());
                },
            ],
            'Inherited properties' => [
                <<<'EOT'
<?php

class ParentParentClass extends NonExisting
{
    public $property5;
}

class ParentClass extends ParentParentClass
{
    private $property1;
    protected $property2;
    public $property3;
    public $property4;
}

class Foobar extends ParentClass
{
    public $property4; // overrides from previous
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertEquals(
                        ['property5', 'property2', 'property3', 'property4'],
                        $properties->keys()
                    );
                },
            ],
            'Return type from docblock' => [
                <<<'EOT'
<?php

use Acme\Post;

class Foobar
{
    /**
     * @var Post
     */
    private $property1;
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertEquals(Type::class(ClassName::fromString('Acme\Post')), $properties->get('property1')->type());
                    $this->assertFalse($properties->get('property1')->isStatic());
                },
            ],
            'Return true if property is static' => [
                <<<'EOT'
<?php

use Acme\Post;

class Foobar
{
    private static $property1;
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertTrue($properties->get('property1')->isStatic());
                },
            ],
        ];
    }
}
