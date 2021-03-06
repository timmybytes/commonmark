<?php

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Extension\Mention;

use League\CommonMark\Environment\ConfigurableEnvironmentInterface;
use League\CommonMark\Exception\InvalidOptionException;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Extension\Mention\Generator\MentionGeneratorInterface;

final class MentionExtension implements ExtensionInterface
{
    public function register(ConfigurableEnvironmentInterface $environment): void
    {
        $mentions = $environment->getConfig('mentions', []);
        foreach ($mentions as $name => $mention) {
            foreach (['prefix', 'regex', 'generator'] as $key) {
                if (! \array_key_exists($key, $mention)) {
                    throw new InvalidOptionException(\sprintf('Required option "mentions/%s/%s" for the Mention extension is missing', $name, $key));
                }
            }

            if (! self::isAValidPartialRegex($mention['regex'])) {
                throw InvalidOptionException::forConfigOption(\sprintf('mentions/%s/regex', $name), $mention['regex'], 'Invalid partial regex. Make sure to exclude starting/ending delimiters and flags.');
            }

            if ($mention['generator'] instanceof MentionGeneratorInterface) {
                $environment->addInlineParser(new MentionParser($mention['prefix'], $mention['regex'], $mention['generator']));
            } elseif (\is_string($mention['generator'])) {
                $environment->addInlineParser(MentionParser::createWithStringTemplate($mention['prefix'], $mention['regex'], $mention['generator']));
            } elseif (\is_callable($mention['generator'])) {
                $environment->addInlineParser(MentionParser::createWithCallback($mention['prefix'], $mention['regex'], $mention['generator']));
            } else {
                throw new InvalidOptionException(\sprintf('The "generator" provided for the "%s" MentionParser configuration must be a string template, callable, or an object that implements %s.', $name, MentionGeneratorInterface::class));
            }
        }
    }

    private static function isAValidPartialRegex(string $regex): bool
    {
        $regex = '/' . $regex . '/i';

        return @\preg_match($regex, '') !== false;
    }
}
