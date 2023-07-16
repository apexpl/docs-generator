<?php
declare(strict_types = 1);

namespace Apex\Docs\SyrusTags;

use Apex\Syrus\Syrus;
use Apex\Syrus\Parser\StackElement;
use Apex\Syrus\Interfaces\TagInterface;
use michelf\markdown;
use Michelf\MarkdownExtra;

/**
 * Renders a specific template tag.  Please see developer documentation for details.
 */
class t_docs_function_list implements TagInterface
{

    #[Inject(Syrus::class)]
    private Syrus $view;

    /**
     * Render
     */
    public function render(string $html, StackElement $e):string
    {

        // Initialize
        $this->view->clearBlock('methods');
        $href_prefix = $e->getAttr('prefix') ?? '';

        // Go through methods
        foreach ($e->getChildren('method') as $method) {
            $prefix = $method->getAttr('visibility');
            if ($method->getAttr('is_static') == 1) {
                $prefix .= ' static';
            }

            $this->view->addBlock('methods', [
                'prefix' => $prefix,
                'uri' => $href_prefix . strtolower($method->getAttr('name')),
                'name' => $method->getAttr('name'),
                'signature' => $method->getAttr('signature') ?? '',
                'return_type' => $method->getAttr('return_type') ?? '',
                'description' => $method->getAttr('desc')
            ]);
        }

        // See also
        $has_seealso = 0;
        foreach ($e->getChildren('also') as $also) {
            $this->view->addBlock('also', [
                'contents' => MarkdownExtra::defaultTransform($also->getBody())
            ]);
            $has_seealso = 1;
        }

        // Template variables
        $this->view->assign('has_seealso', $has_seealso);

        // Return
        $html = file_get_contents(__DIR__ . '/../../themes/syrus/template_class.html');
        return $this->view->renderBlock($html);
    }

}



