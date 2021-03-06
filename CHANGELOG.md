# Change Log
All notable changes to this project will be documented in this file.
Updates should follow the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [Unreleased][unreleased]

See <https://commonmark.thephpleague.com/2.0/upgrading/> for detailed information on upgrading to version 2.0.

### Added

 - Added new `FrontMatterExtension` ([see documentation](https://commonmark.thephpleague.com/extensions/front-matter/))
 - Added the ability to delegate event dispatching to PSR-14 compliant event dispatcher libraries
 - Added the ability to configure disallowed raw HTML tags (#507)
 - Added the ability for Mentions to use multiple characters for their symbol (#514, #550)
 - Added `heading_permalink/min_heading_level` and `heading_permalink/max_heading_level` options to control which headings get permalinks (#519)
 - Added `footnote/backref_symbol` option for customizing backreference link appearance (#522)
 - Added new `HtmlFilter` and `StringContainerHelper` utility classes
 - Added new `AbstractBlockContinueParser` class to simplify the creation of custom block parsers
 - Added several new classes and interfaces:
   - `BlockContinue`
   - `BlockContinueParserInterface`
   - `BlockStart`
   - `BlockStartParserInterface`
   - `ChildNodeRendererInterface`
   - `CursorState`
   - `DashParser` (extracted from `PunctuationParser`)
   - `DelimiterParser`
   - `DocumentBlockParser`
   - `DocumentRenderedEvent`
   - `EllipsesParser` (extracted from `PunctuationParser`)
   - `HtmlRendererInterface`
   - `InlineParserEngineInterface`
   - `InlineParserMatch`
   - `MarkdownParserState`
   - `MarkdownParserStateInterface`
   - `ReferenceableInterface`
   - `RenderedContent`
   - `RenderedContentInterface`
 - Added several new methods:
   - `Environment::setEventDispatcher()`
   - `EnvironmentInterface::getInlineParsers()`
   - `FencedCode::setInfo()`
   - `Heading::setLevel()`
   - `HtmlRenderer::renderDocument()`
   - `InlineParserContext::getFullMatch()`
   - `InlineParserContext::getFullMatchLength()`
   - `InlineParserContext::getMatches()`
   - `InlineParserContext::getSubMatches()`
   - `InvalidOptionException::forConfigOption()`
   - `InvalidOptionException::forParameter()`
   - `LinkParserHelper::parsePartialLinkLabel()`
   - `LinkParserHelper::parsePartialLinkTitle()`
   - `RegexHelper::isLetter()`
   - `StringContainerInterface::setLiteral()`
   - `TableCell::getType()`
   - `TableCell::setType()`
   - `TableCell::getAlign()`
   - `TableCell::setAlign()`
 - Added purity markers throughout the codebase (verified with Psalm)

### Changed

 - `CommonMarkConverter::convertToHtml()` now returns an instance of `RenderedContentInterface`. This can be cast to a string for backward compatibility with 1.x.
 - Changes to configuration options:
     - `mentions/*/symbol` has been renamed to `mentions/*/prefix`
     - `mentions/*/regex` now requires partial regular expressions (without delimiters or flags)
 - Event dispatching is now fully PSR-14 compliant
 - Moved and renamed several classes - [see the full list here](https://commonmark.thephpleague.com/2.0/upgrading/#classesnamespaces-renamed)
 - Implemented a new approach to block parsing. This was a massive change, so here are the highlights:
   - Functionality previously found in block parsers and node elements has moved to block parser factories and block parsers, respectively ([more details](https://commonmark.thephpleague.com/2.0/upgrading/#new-block-parsing-approach))
   - `ConfigurableEnvironmentInterface::addBlockParser()` is now `ConfigurableEnvironmentInterface::addBlockParserFactory()`
   - `ReferenceParser` was re-implemented and works completely different than before
   - The paragraph parser no longer needs to be added manually to the environment
 - Implemented a new approach to inline parsing where parsers can now specify longer strings or regular expressions they want to parse (instead of just single characters):
   - `InlineParserInterface::getCharacters()` is now `getMatchDefinition()` and returns an instance of `InlineParserMatch`
   - `InlineParserContext::__construct()` now requires the contents to be provided as a `Cursor` instead of a `string`
 - Implemented delimiter parsing as a special type of inline parser (via the new `DelimiterParser` class)
 - Changed block and inline rendering to use common methods and interfaces
   - `BlockRendererInterface` and `InlineRendererInterface` were replaced by `NodeRendererInterface` with slightly different parameters. All core renderers now implement this interface.
   - `ConfigurableEnvironmentInterface::addBlockRenderer()` and `addInlineRenderer()` are now just `addRenderer()`
   - `EnvironmentInterface::getBlockRenderersForClass()` and `getInlineRenderersForClass()` are now just `getRenderersForClass()`
 - Re-implemented the GFM Autolink extension using the new inline parser approach instead of document processors
   - `EmailAutolinkProcessor` is now `EmailAutolinkParser`
   - `UrlAutolinkProcessor` is now `UrlAutolinkParser`
 - Combined separate classes/interfaces into one:
   - `DisallowedRawHtmlRenderer` replaces `DisallowedRawHtmlBlockRenderer` and `DisallowedRawHtmlInlineRenderer`
   - `NodeRendererInterface` replaces `BlockRendererInterface` and `InlineRendererInterface`
 - Renamed the following methods:
   - `Environment` and `ConfigurableEnvironmentInterface`:
     - `addBlockParser()` is now `addBlockStartParser()`
   - `ReferenceMap` and `ReferenceMapInterface`:
     - `addReference()` is now `add()`
     - `getReference()` is now `get()`
     - `listReferences()` is now `getIterator()`
   - Various node (block/inline) classes:
     - `getContent()` is now `getLiteral()`
     - `setContent()` is now `setLiteral()`
 - Moved and renamed the following constants:
   - `EnvironmentInterface::HTML_INPUT_ALLOW` is now `HtmlFilter::ALLOW`
   - `EnvironmentInterface::HTML_INPUT_ESCAPE` is now `HtmlFilter::ESCAPE`
   - `EnvironmentInterface::HTML_INPUT_STRIP` is now `HtmlFilter::STRIP`
 - Changed the visibility of the following properties:
   - `TableCell::$align` is now `private`
   - `TableCell::$type` is now `private`
   - `TableSection::$type` is now `private`
 - Added missing return types to virtually every class and interface method
 - Several methods which previously returned `$this` now return `void`
   - `Delimiter::setPrevious()`
   - `Node::replaceChildren()`
   - `Context::setTip()`
   - `Context::setContainer()`
   - `Context::setBlocksParsed()`
   - `AbstractStringContainer::setContent()`
   - `AbstractWebResource::setUrl()`
 - `Heading` nodes no longer directly contain a copy of their inner text
 - `StringContainerInterface` can now be used for inlines, not just blocks
 - `ArrayCollection` is now final and only supports integer keys
 - `Cursor::saveState()` and `Cursor::restoreState()` now use `CursorState` objects instead of arrays
 - `NodeWalker::next()` now enters, traverses any children, and leaves all elements which may have children (basically all blocks plus any inlines with children). Previously, it only did this for elements explicitly marked as "containers".
 - `InvalidOptionException` now extends from `UnexpectedValueException`
 - Anything with a `getReference(): ReferenceInterface` method now implements `ReferencableInterface`
 - Several changes made to the Footnote extension:
   - Footnote identifiers can no longer contain spaces
   - Anonymous footnotes can now span subsequent lines
   - Footnotes can now contain multiple lines of content, including sub-blocks, by indenting them
   - Footnote event listeners now have numbered priorities (but still execute in the same order)
   - Footnotes must now be separated from previous content by a blank line
 - The line numbers (keys) returned via `MarkdownInput::getLines()` now start at 1 instead of 0
 - `DelimiterProcessorCollectionInterface` now extends `Countable`
 - `RegexHelper::PARTIAL_` constants must always be used in case-insensitive contexts

### Fixed

 - Fixed parsing of footnotes without content
 - Fixed rendering of orphaned footnotes and footnote refs
 - Fixed some URL autolinks breaking too early (#492)

### Removed

 - Removed support for PHP 7.1
 - Removed all previously-deprecated functionality:
   - Removed the `Converter` class and `ConverterInterface`
   - Removed the `bin/commonmark` script
   - Removed the `Html5Entities` utility class
   - Removed the `InlineMentionParser` (use `MentionParser` instead)
   - Removed `DefaultSlugGenerator` and `SlugGeneratorInterface` from the `Extension/HeadingPermalink/Slug` sub-namespace (use the new ones under `./SlugGenerator` instead)
   - Removed the following `ArrayCollection` methods:
     - `add()`
     - `set()`
     - `get()`
     - `remove()`
     - `isEmpty()`
     - `contains()`
     - `indexOf()`
     - `containsKey()`
     - `replaceWith()`
     - `removeGaps()`
   - Removed the `ListBlock::TYPE_UNORDERED` constant
   - Removed the `CommonMarkConverter::VERSION` constant
   - Removed the `HeadingPermalinkRenderer::DEFAULT_INNER_CONTENTS` constant
   - Removed the `heading_permalink/inner_contents` configuration option
 - Removed now-unused classes:
   - `AbstractStringContainerBlock`
   - `BlockRendererInterface`
   - `Context`
   - `ContextInterface`
   - `Converter`
   - `ConverterInterface`
   - `InlineRendererInterface`
   - `PunctuationParser` (was split into two classes: `DashParser` and `EllipsesParser`)
   - `UnmatchedBlockCloser`
 - Removed the following methods, properties, and constants:
   - `AbstractBlock::$open`
   - `AbstractBlock::$lastLineBlank`
   - `AbstractBlock::isContainer()`
   - `AbstractBlock::canContain()`
   - `AbstractBlock::isCode()`
   - `AbstractBlock::matchesNextLine()`
   - `AbstractBlock::endsWithBlankLine()`
   - `AbstractBlock::setLastLineBlank()`
   - `AbstractBlock::shouldLastLineBeBlank()`
   - `AbstractBlock::isOpen()`
   - `AbstractBlock::finalize()`
   - `ConfigurableEnvironmentInterface::addBlockParser()`
   - `Delimiter::setCanClose()`
   - `EnvironmentInterface::getInlineParsersForCharacter()`
   - `EnvironmentInterface::getInlineParserCharacterRegex()`
   - `HtmlRenderer::renderBlock()`
   - `HtmlRenderer::renderBlocks()`
   - `HtmlRenderer::renderInline()`
   - `HtmlRenderer::renderInlines()`
   - `Node::isContainer()`
   - `RegexHelper::matchAll()` (use the new `matchFirst()` method instead)
   - `RegexHelper::REGEX_WHITESPACE`
 - Removed the second `$contents` argument from the `Heading` constructor

[unreleased]: https://github.com/thephpleague/commonmark/compare/1.5...latest
