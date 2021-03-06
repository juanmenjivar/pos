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

use Symfony\Component\Validator\Constraints\IdenticalTo;
use Symfony\Component\Validator\Constraints\IdenticalToValidator;
use Symfony\Component\Validator\Validation;

/**
 * @author Daniel Holmes <daniel@danielholmes.org>
 */
class IdenticalToValidatorTest extends AbstractComparisonValidatorTestCase
{
    protected function getApiVersion()
    {
        return Validation::API_VERSION_2_5;
    }

    protected function createValidator()
    {
        return new IdenticalToValidator();
    }

    protected function createConstraint(array $options)
    {
        return new IdenticalTo($options);
    }

    /**
     * {@inheritdoc}
     */
    public function provideValidComparisons()
    {
        $date = new \DateTime('2000-01-01');
        $object = new ComparisonTest_Class(2);

        return array(
            array(3, 3),
            array('a', 'a'),
            array($date, $date),
            array($object, $object),
            array(null, 1),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function provideInvalidComparisons()
    {
        return array(
            array(1, '1', 2, '2', 'integer'),
            array(2, '2', '2', '"2"', 'string'),
            array('22', '"22"', '333', '"333"', 'string'),
            array(new \DateTime('2001-01-01'), 'Jan 1, 2001, 12:00 AM', new \DateTime('2001-01-01'), 'Jan 1, 2001, 12:00 AM', 'DateTime'),
            array(new \DateTime('2001-01-01'), 'Jan 1, 2001, 12:00 AM', new \DateTime('1999-01-01'), 'Jan 1, 1999, 12:00 AM', 'DateTime'),
            array(new ComparisonTest_Class(4), '4', new ComparisonTest_Class(5), '5', __NAMESPACE__.'\ComparisonTest_Class'),
        );
    }
}
