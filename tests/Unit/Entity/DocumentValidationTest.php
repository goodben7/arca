<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Document;
use App\Enum\EntityType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DocumentValidationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidDocumentPassesValidation(): void
    {
        $document = $this->createValidDocument();

        $violations = $this->validator->validate($document);

        self::assertCount(0, $violations);
    }

    public function testAllDocumentTypesAreAccepted(): void
    {
        foreach (array_values(Document::getTypeAsChoices()) as $type) {
            $document = $this->createValidDocument();
            $document->setType($type);

            $violations = $this->validator->validate($document);

            self::assertCount(0, $violations, sprintf('Type %s should be valid', $type));
        }
    }

    public function testAllHolderTypesAreAccepted(): void
    {
        foreach (EntityType::getAll() as $holderType) {
            $document = $this->createValidDocument();
            $document->setHolderType($holderType);

            $violations = $this->validator->validate($document);

            self::assertCount(0, $violations, sprintf('Holder type %s should be valid', $holderType));
        }
    }

    public function testInvalidTypeFailsValidation(): void
    {
        $document = $this->createValidDocument();
        $document->setType('INVALID');

        $violations = $this->validator->validate($document);

        self::assertGreaterThan(0, $violations->count());
    }

    public function testMissingTypeFailsValidation(): void
    {
        $document = $this->createValidDocument();
        $type = new \ReflectionProperty(Document::class, 'type');
        $type->setValue($document, null);

        $violations = $this->validator->validate($document);

        self::assertGreaterThan(0, $violations->count());
    }

    public function testInvalidHolderTypeFailsValidation(): void
    {
        $document = $this->createValidDocument();
        $document->setHolderType('UNKNOWN');

        $violations = $this->validator->validate($document);

        self::assertGreaterThan(0, $violations->count());
    }

    public function testMissingHolderTypeFailsValidation(): void
    {
        $document = $this->createValidDocument();
        $holderType = new \ReflectionProperty(Document::class, 'holderType');
        $holderType->setValue($document, null);

        $violations = $this->validator->validate($document);

        self::assertGreaterThan(0, $violations->count());
    }

    public function testMissingHolderIdFailsValidation(): void
    {
        $document = $this->createValidDocument();
        $document->setHolderId('');

        $violations = $this->validator->validate($document);

        self::assertGreaterThan(0, $violations->count());
    }

    public function testOptionalFieldsCanBeNull(): void
    {
        $document = $this->createValidDocument();
        $document
            ->setTitle(null)
            ->setDocumentRefNumber(null);

        $violations = $this->validator->validate($document);

        self::assertCount(0, $violations);
    }

    public function testSetCreatedAtValueSetsUploadedAtOnPersist(): void
    {
        $document = $this->createValidDocument();

        self::assertNull($document->getUploadedAt());

        $document->setCreatedAtValue();

        self::assertInstanceOf(\DateTimeImmutable::class, $document->getUploadedAt());
    }

    public function testUpdateUpdatedAtSetsTimestampOnUpdate(): void
    {
        $document = $this->createValidDocument();
        $document->setUploadedAt(new \DateTimeImmutable('2025-06-01'));

        self::assertNull($document->getUpdatedAt());

        $document->updateUpdatedAt();

        self::assertInstanceOf(\DateTimeImmutable::class, $document->getUpdatedAt());
    }

    public function testSetFileUpdatesUpdatedAtWhenFileProvided(): void
    {
        $document = $this->createValidDocument();
        $file = $this->createTempFile('contract.pdf');

        self::assertNull($document->getUpdatedAt());

        $document->setFile($file);

        self::assertSame($file, $document->getFile());
        self::assertInstanceOf(\DateTimeImmutable::class, $document->getUpdatedAt());
    }

    public function testSetFileSecondaryUpdatesUpdatedAtWhenFileProvided(): void
    {
        $document = $this->createValidDocument();
        $file = $this->createTempFile('annex.pdf');

        self::assertNull($document->getUpdatedAt());

        $document->setFileSecondary($file);

        self::assertSame($file, $document->getFileSecondary());
        self::assertInstanceOf(\DateTimeImmutable::class, $document->getUpdatedAt());
    }

    public function testSetFileDoesNotUpdateUpdatedAtWhenFileIsNull(): void
    {
        $document = $this->createValidDocument();

        $document->setFile(null);

        self::assertNull($document->getUpdatedAt());
    }

    public function testContentUrlsCanBeSetForSerialization(): void
    {
        $document = $this->createValidDocument();
        $document
            ->setContentUrl('/media/contract.pdf')
            ->setContentUrlSecondary('/media/annex.pdf');

        self::assertSame('/media/contract.pdf', $document->getContentUrl());
        self::assertSame('/media/annex.pdf', $document->getContentUrlSecondary());
    }

    public function testGetTypeAsChoicesContainsAllKnownTypes(): void
    {
        $choices = Document::getTypeAsChoices();

        self::assertContains(Document::TYPE_ID_CARD, $choices);
        self::assertContains(Document::TYPE_CONTRACT, $choices);
        self::assertContains(Document::TYPE_PAYSLIP, $choices);
        self::assertContains(Document::TYPE_OTHER, $choices);
        self::assertCount(27, $choices);
    }

    public function testIdPrefixConstant(): void
    {
        self::assertSame('DC', Document::ID_PREFIX);
    }

    private function createValidDocument(): Document
    {
        return (new Document())
            ->setType(Document::TYPE_CONTRACT)
            ->setHolderType(EntityType::EMPLOYEE)
            ->setHolderId('EMTEST001')
            ->setTitle('Contrat CDI')
            ->setDocumentRefNumber('CNTR-2025-001');
    }

    private function createTempFile(string $filename): File
    {
        $path = sys_get_temp_dir().'/'.uniqid('doc_', true).'_'.$filename;
        file_put_contents($path, 'test content');

        return new File($path);
    }
}
