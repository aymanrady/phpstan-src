<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\Generic\TemplateTypeMap;
use function count;

/**
 * @implements Rule<InClassMethodNode>
 */
class MethodConditionalReturnTypeRule implements Rule
{

	public function __construct(private ConditionalReturnTypeRuleHelper $helper)
	{
	}

	public function getNodeType(): string
	{
		return InClassMethodNode::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$method = $scope->getFunction();
		if ($method === null) {
			throw new ShouldNotHappenException();
		}

		$variants = $method->getVariants();
		if (count($variants) !== 1) {
			return [];
		}

		$templateTypes = $variants[0]->getTemplateTypeMap()->getTypes();

		if ($method instanceof MethodReflection) {
			$templateTypes = [
				...$method->getDeclaringClass()->getTemplateTypeMap()->getTypes(),
				...$templateTypes,
			];
		}

		return $this->helper->check(
			$variants[0],
			new TemplateTypeMap($templateTypes),
		);
	}

}
