<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\ContentUpdater\Action;

use eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\RemoveField;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Mapper as ContentMapper;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway;
use PHPUnit\Framework\TestCase;

/**
 * Test case for Content Type Updater.
 */
class RemoveFieldTest extends TestCase
{
    /**
     * Content gateway mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $contentGatewayMock;

    /**
     * Content gateway mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected $contentStorageHandlerMock;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\Mapper */
    protected $contentMapperMock;

    /**
     * RemoveField action to test.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\RemoveField
     */
    protected $removeFieldAction;

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\RemoveField::apply
     */
    public function testApplySingleVersionSingleTranslation()
    {
        $contentId = 42;
        $versionNumbers = [1];
        $action = $this->getRemoveFieldAction();
        $fieldId = 3;
        $content = $this->getContentFixture(1, ['cro-HR' => $fieldId]);

        $this->getContentGatewayMock()
            ->expects($this->once())
            ->method('listVersionNumbers')
            ->with($this->equalTo($contentId))
            ->will($this->returnValue($versionNumbers));

        $this->getContentGatewayMock()
            ->expects($this->once())
            ->method('loadVersionedNameData')
            ->with($this->equalTo([['id' => $contentId, 'version' => 1]]))
            ->will($this->returnValue([]));

        $this->getContentGatewayMock()
            ->expects($this->at(2))
            ->method('load')
            ->with($contentId, 1)
            ->will($this->returnValue([]));

        $this->getContentMapperMock()
            ->expects($this->once())
            ->method('extractContentFromRows')
            ->with([], [])
            ->will($this->returnValue([$content]));

        $this->getContentGatewayMock()
            ->expects($this->once())
            ->method('deleteField')
            ->with($this->equalTo($fieldId));

        $this->getContentStorageHandlerMock()->expects($this->once())
            ->method('deleteFieldData')
            ->with(
                $this->equalTo('ezstring'),
                $content->versionInfo,
                $this->equalTo([$fieldId])
            );

        $action->apply($contentId);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\RemoveField::apply
     */
    public function testApplyMultipleVersionsSingleTranslation()
    {
        $contentId = 42;
        $versionNumbers = [1, 2];
        $action = $this->getRemoveFieldAction();
        $fieldId = 3;
        $content1 = $this->getContentFixture(1, ['cro-HR' => $fieldId]);
        $content2 = $this->getContentFixture(2, ['cro-HR' => $fieldId]);

        $this->getContentGatewayMock()
            ->expects($this->once())
            ->method('listVersionNumbers')
            ->with($this->equalTo($contentId))
            ->will($this->returnValue($versionNumbers));

        $this->getContentGatewayMock()
            ->expects($this->once())
            ->method('loadVersionedNameData')
            ->with($this->equalTo([['id' => $contentId, 'version' => 1], ['id' => $contentId, 'version' => 2]]))
            ->will($this->returnValue([]));

        $this->getContentGatewayMock()
            ->expects($this->at(2))
            ->method('load')
            ->with($contentId, 1)
            ->will($this->returnValue([]));

        $this->getContentMapperMock()
            ->expects($this->at(0))
            ->method('extractContentFromRows')
            ->with([], [])
            ->will($this->returnValue([$content1]));

        $this->getContentGatewayMock()
            ->expects($this->at(3))
            ->method('load')
            ->with($contentId, 2)
            ->will($this->returnValue([]));

        $this->getContentMapperMock()
            ->expects($this->at(1))
            ->method('extractContentFromRows')
            ->with([], [])
            ->will($this->returnValue([$content2]));

        $this->getContentGatewayMock()
            ->expects($this->once())
            ->method('deleteField')
            ->with($this->equalTo($fieldId));

        $this->getContentStorageHandlerMock()
            ->expects($this->at(0))
            ->method('deleteFieldData')
            ->with(
                $this->equalTo('ezstring'),
                $content1->versionInfo,
                $this->equalTo([$fieldId])
            );

        $this->getContentStorageHandlerMock()
            ->expects($this->at(1))
            ->method('deleteFieldData')
            ->with(
                $this->equalTo('ezstring'),
                $content2->versionInfo,
                $this->equalTo([$fieldId])
            );

        $action->apply($contentId);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\RemoveField::apply
     */
    public function testApplyMultipleVersionsMultipleTranslations()
    {
        $contentId = 42;
        $versionNumbers = [1, 2];
        $action = $this->getRemoveFieldAction();
        $fieldId1 = 3;
        $fieldId2 = 4;
        $content1 = $this->getContentFixture(1, ['cro-HR' => $fieldId1, 'hun-HU' => $fieldId2]);
        $content2 = $this->getContentFixture(2, ['cro-HR' => $fieldId1, 'hun-HU' => $fieldId2]);

        $this->getContentGatewayMock()
            ->expects($this->once())
            ->method('listVersionNumbers')
            ->with($this->equalTo($contentId))
            ->will($this->returnValue($versionNumbers));

        $this->getContentGatewayMock()
            ->expects($this->once())
            ->method('loadVersionedNameData')
            ->with($this->equalTo([['id' => $contentId, 'version' => 1], ['id' => $contentId, 'version' => 2]]))
            ->will($this->returnValue([]));

        $this->getContentGatewayMock()
            ->expects($this->at(2))
            ->method('load')
            ->with($contentId, 1)
            ->will($this->returnValue([]));

        $this->getContentMapperMock()
            ->expects($this->at(0))
            ->method('extractContentFromRows')
            ->with([], [])
            ->will($this->returnValue([$content1]));

        $this->getContentGatewayMock()
            ->expects($this->at(3))
            ->method('load')
            ->with($contentId, 2)
            ->will($this->returnValue([]));

        $this->getContentMapperMock()
            ->expects($this->at(1))
            ->method('extractContentFromRows')
            ->with([], [])
            ->will($this->returnValue([$content2]));

        $this->getContentGatewayMock()
            ->expects($this->at(5))
            ->method('deleteField')
            ->with($this->equalTo($fieldId1));

        $this->getContentGatewayMock()
            ->expects($this->at(6))
            ->method('deleteField')
            ->with($this->equalTo($fieldId2));

        $this->getContentStorageHandlerMock()
            ->expects($this->at(0))
            ->method('deleteFieldData')
            ->with(
                $this->equalTo('ezstring'),
                $content1->versionInfo,
                $this->equalTo([$fieldId1, $fieldId2])
            );

        $this->getContentStorageHandlerMock()
            ->expects($this->at(1))
            ->method('deleteFieldData')
            ->with(
                $this->equalTo('ezstring'),
                $content2->versionInfo,
                $this->equalTo([$fieldId1, $fieldId2])
            );

        $this->getContentGatewayMock()
            ->expects($this->at(4))
            ->method('removeRelationsByFieldDefinitionId')
            ->with($this->equalTo(42));

        $action->apply($contentId);
    }

    protected function getContentFixture(int $versionNo, array $languageCodes): Content
    {
        $fields = [];

        foreach ($languageCodes as $languageCode => $fieldId) {
            $fieldNoRemove = new Content\Field();
            $fieldNoRemove->id = 2;
            $fieldNoRemove->versionNo = $versionNo;
            $fieldNoRemove->fieldDefinitionId = 23;
            $fieldNoRemove->type = 'ezstring';
            $fieldNoRemove->languageCode = $languageCode;

            $fields[] = $fieldNoRemove;

            $fieldRemove = new Content\Field();
            $fieldRemove->id = $fieldId;
            $fieldRemove->versionNo = $versionNo;
            $fieldRemove->fieldDefinitionId = 42;
            $fieldRemove->type = 'ezstring';
            $fieldRemove->languageCode = $languageCode;

            $fields[] = $fieldRemove;
        }

        $content = new Content();
        $content->versionInfo = new Content\VersionInfo();
        $content->fields = $fields;
        $content->versionInfo->versionNo = $versionNo;

        return $content;
    }

    /**
     * Returns a Content Gateway mock.
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected function getContentGatewayMock()
    {
        if (!isset($this->contentGatewayMock)) {
            $this->contentGatewayMock = $this->createMock(Gateway::class);
        }

        return $this->contentGatewayMock;
    }

    /**
     * Returns a Content StorageHandler mock.
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected function getContentStorageHandlerMock()
    {
        if (!isset($this->contentStorageHandlerMock)) {
            $this->contentStorageHandlerMock = $this->createMock(StorageHandler::class);
        }

        return $this->contentStorageHandlerMock;
    }

    /**
     * Returns a Content mapper mock.
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected function getContentMapperMock()
    {
        if (!isset($this->contentMapperMock)) {
            $this->contentMapperMock = $this->createMock(ContentMapper::class);
        }

        return $this->contentMapperMock;
    }

    /**
     * Returns a FieldDefinition fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    protected function getFieldDefinitionFixture()
    {
        $fieldDef = new Content\Type\FieldDefinition();
        $fieldDef->id = 42;
        $fieldDef->fieldType = 'ezstring';
        $fieldDef->defaultValue = new Content\FieldValue();

        return $fieldDef;
    }

    /**
     * Returns the RemoveField action to test.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\RemoveField
     */
    protected function getRemoveFieldAction()
    {
        if (!isset($this->removeFieldAction)) {
            $this->removeFieldAction = new RemoveField(
                $this->getContentGatewayMock(),
                $this->getFieldDefinitionFixture(),
                $this->getContentStorageHandlerMock(),
                $this->getContentMapperMock()
            );
        }

        return $this->removeFieldAction;
    }
}
