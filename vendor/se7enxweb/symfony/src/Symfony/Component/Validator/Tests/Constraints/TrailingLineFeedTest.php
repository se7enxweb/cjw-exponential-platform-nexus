<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Tests\Constraints;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\CardScheme;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Time;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validation;

/**
 * A "$" anchor also matches before a final line feed: values that only become valid
 * once that line feed is dropped must be rejected (CVE-2024-50343).
 */
class TrailingLineFeedTest extends TestCase
{
    /**
     * @dataProvider provideValidValues
     */
    public function testValidValueIsAccepted($value, Constraint $constraint)
    {
        $this->assertCount(0, Validation::createValidator()->validate($value, $constraint));
    }

    /**
     * @dataProvider provideValidValues
     */
    public function testValidValueFollowedByLineFeedIsRejected($value, Constraint $constraint)
    {
        $this->assertCount(1, Validation::createValidator()->validate($value."\n", $constraint));
    }

    public function provideValidValues()
    {
        return [
            ['2010-01-01', new Date()],
            ['09:36:10', new Time()],
            ['user@example.com', new Email()],
            ['http://www.example.com', new Url()],
            ['4111111111111111', new CardScheme(['schemes' => ['VISA']])],
        ];
    }
}
