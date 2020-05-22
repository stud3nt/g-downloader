<?php

namespace App\Tests\Unit\Repository;

use App\Converter\ModelConverter;
use App\Entity\Parser\Node;
use App\Enum\NodeLevel;
use App\Enum\ParserType;
use App\Model\ParsedNode;
use App\Repository\NodeRepository;
use App\Utils\TestsHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NodeRepositoryTest extends KernelTestCase
{
    /** @var ContainerInterface */
    protected $containerInstance;

    /** @var NodeRepository */
    protected $nodeRepository;

    public function setUp(): void
    {
        $this->containerInstance = self::bootKernel()->getContainer();
        $this->nodeRepository = $this->containerInstance->get(NodeRepository::class);
    }

    public function testFindOneByParsedNode()
    {
        $parsedNode = new ParsedNode();
        $parsedNodeData = TestsHelper::generateNodeArray();

        $modelConverter = new ModelConverter();
        $modelConverter->setData($parsedNodeData, $parsedNode);

        $parsedNode->setLevel(NodeLevel::Board);
        $parsedNode->setParser(ParserType::Reddit);
        $parsedNode->setIdentifier('r_AmateurGroups');

        $node = $this->nodeRepository->findOneByParsedNode($parsedNode);

        $this->assertNotNull($node);
        $this->assertInstanceOf(Node::class, $node);
        $this->assertEquals(NodeLevel::Board, $node->getLevel());
        $this->assertEquals('r_AmateurGroups', $node->getIdentifier());
    }
}