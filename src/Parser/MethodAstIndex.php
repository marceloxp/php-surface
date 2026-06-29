<?php

declare(strict_types=1);

namespace PhpSurface\Parser;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

final class MethodAstIndex
{
    /**
     * @return array<int, array{endLine: int, isAbstract: bool}>
     */
    public function index(string $filePath): array
    {
        $source = file_get_contents($filePath);
        if ($source === false) {
            throw new \RuntimeException(sprintf('Unable to read file: %s', $filePath));
        }

        $parser = (new ParserFactory())->createForHostVersion();
        $ast = $parser->parse($source);
        if ($ast === null) {
            return [];
        }

        $index = [];
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class ($index) extends NodeVisitorAbstract {
            /**
             * @param array<int, array{endLine: int, isAbstract: bool}> $index
             */
            public function __construct(private array &$index)
            {
            }

            public function enterNode(Node $node): void
            {
                if (!$node instanceof ClassMethod) {
                    return;
                }

                $startLine = $node->getStartLine();
                $this->index[$startLine] = [
                    'endLine' => $node->getEndLine(),
                    'isAbstract' => $node->isAbstract(),
                ];
            }
        });
        $traverser->traverse($ast);

        return $index;
    }
}
