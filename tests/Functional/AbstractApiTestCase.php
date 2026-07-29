<?php

namespace App\Tests\Functional;

use App\Entity\Employee;
use App\Entity\Department;
use App\Entity\Position;
use App\Entity\Profile;
use App\Entity\User;
use App\Manager\UserManager;
use App\Model\EmployeeConstants;
use App\Model\PositionLevel;
use App\Model\PositionStatusConstants;
use App\Model\UserProxyIntertace;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;
    protected Connection $connection;
    protected string $projectDir;

    private static bool $schemaInitialized = false;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->connection = $this->entityManager->getConnection();
        $this->projectDir = static::getContainer()->getParameter('kernel.project_dir');

        if (!self::$schemaInitialized) {
            $this->initializeSchema();
            self::$schemaInitialized = true;
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->entityManager)) {
            $this->resetDatabase();
        }

        if (isset($this->projectDir)) {
            $this->cleanUploadDirectory();
        }

        parent::tearDown();
    }

    protected function createSuperAdminUser(string $email = 'func-admin@arca.test', string $password = 'Test1234!'): User
    {
        /** @var UserManager $userManager */
        $userManager = static::getContainer()->get(UserManager::class);

        $user = (new User())
            ->setEmail($email)
            ->setPlainPassword($password)
            ->setPersonType(UserProxyIntertace::PERSON_SUPER_ADMIN)
            ->setIsConfirmed(true);

        return $userManager->create($user);
    }

    /**
     * @param list<string> $permissions
     */
    protected function createUserWithPermissions(
        array $permissions,
        string $email = 'func-user@arca.test',
        string $password = 'Test1234!',
    ): User {
        /** @var UserManager $userManager */
        $userManager = static::getContainer()->get(UserManager::class);

        $profile = (new Profile())
            ->setLabel('Functional test profile')
            ->setPersonType(UserProxyIntertace::PERSON_EMPLOYEE)
            ->setPermission($permissions)
            ->setActive(true)
            ->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($profile);
        $this->entityManager->flush();

        $user = (new User())
            ->setEmail($email)
            ->setPlainPassword($password)
            ->setPersonType($profile->getPersonType())
            ->setProfile($profile)
            ->setIsConfirmed(true);

        return $userManager->create($user);
    }

    protected function createEmployee(string $employeeNumber = 'EMP-FUNC-001'): Employee
    {
        $employee = (new Employee())
            ->setFirstName('Jane')
            ->setLastName('Functional')
            ->setGender(EmployeeConstants::GENDER_FEMALE)
            ->setHireDate(new \DateTimeImmutable('2024-01-01'))
            ->setEmployeeNumber($employeeNumber)
            ->setStatus(EmployeeConstants::STATUS_ACTIVE)
            ->setCreatedAt(new \DateTimeImmutable('2024-01-01'));

        $this->entityManager->persist($employee);
        $this->entityManager->flush();

        return $employee;
    }

    /**
     * Profile required by EmployeeManager when creating an employee via API
     * (linked user account with PERSON_EMPLOYEE).
     */
    protected function createEmployeePersonProfile(string $label = 'Employee functional profile'): Profile
    {
        $profile = (new Profile())
            ->setLabel($label)
            ->setPersonType(UserProxyIntertace::PERSON_EMPLOYEE)
            ->setPermission(['ROLE_USER'])
            ->setActive(true)
            ->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($profile);
        $this->entityManager->flush();

        return $profile;
    }

    protected function createDepartment(string $code = 'IT-FUNC', string $name = 'IT Functional'): Department
    {
        $department = (new Department())
            ->setCode($code)
            ->setName($name);

        $this->entityManager->persist($department);
        $this->entityManager->flush();

        return $department;
    }

    protected function createOpenPosition(
        string $departmentIdOrName,
        string $title = 'Developer Functional',
    ): Position {
        $position = (new Position())
            ->setTitle($title)
            ->setDepartment($departmentIdOrName)
            ->setLevel(PositionLevel::MID_LEVEL)
            ->setHeadcount(1)
            ->setOpenPositions(1)
            ->setStatus(PositionStatusConstants::STATUS_OPEN);

        $this->entityManager->persist($position);
        $this->entityManager->flush();

        return $position;
    }

    protected function authenticate(string $email = 'func-admin@arca.test', string $password = 'Test1234!'): string
    {
        $this->client->request(
            'POST',
            '/api/authentication_token',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'username' => $email,
                'password' => $password,
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('token', $data);

        return $data['token'];
    }

    /**
     * @param array<string, mixed> $parameters
     * @param array<string, string> $headers
     */
    protected function apiRequest(
        string $method,
        string $uri,
        array $parameters = [],
        array $headers = [],
    ): void {
        $server = array_merge([
            'HTTP_ACCEPT' => 'application/json',
        ], $headers);

        if ([] !== $parameters && \in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $this->client->request(
                $method,
                $uri,
                server: array_merge(
                    ['CONTENT_TYPE' => 'application/json'],
                    $server,
                ),
                content: json_encode($parameters, JSON_THROW_ON_ERROR),
            );

            return;
        }

        $this->client->request($method, $uri, $parameters, [], $server);
    }

    /**
     * @param array<string, string> $fields
     * @param array<string, string> $filePaths keyed by field name (e.g. file)
     * @param array<string, string> $headers
     */
    protected function apiMultipartRequest(
        string $uri,
        array $fields,
        array $filePaths,
        array $headers = [],
    ): void {
        $uploadedFiles = [];

        foreach ($filePaths as $fieldName => $path) {
            $uploadedFiles[$fieldName] = new UploadedFile(
                $path,
                basename($path),
                test: true,
            );
        }

        $this->client->request(
            'POST',
            $uri,
            $fields,
            $uploadedFiles,
            array_merge([
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'multipart/form-data',
            ], $headers),
        );
    }

    protected function createTempFile(string $filename, string $content = 'functional test file'): string
    {
        $path = sys_get_temp_dir().'/'.uniqid('arca_func_', true).'_'.$filename;
        file_put_contents($path, $content);

        return $path;
    }

    /**
     * @return array<string, mixed>
     */
    protected function decodeJsonResponse(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    private function resetDatabase(): void
    {
        $this->entityManager->clear();

        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    private function initializeSchema(): void
    {
        $this->resetDatabase();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getCollectionMembers(array $payload): array
    {
        if (isset($payload['member'])) {
            return $payload['member'];
        }

        return array_is_list($payload) ? $payload : [];
    }

    private function cleanUploadDirectory(): void
    {
        $directory = $this->projectDir.'/var/test_uploads';

        if (!is_dir($directory)) {
            return;
        }

        foreach (glob($directory.'/*') ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
