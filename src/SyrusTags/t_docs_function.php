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
class t_docs_function implements TagInterface
{

    #[Inject(Syrus::class)]
    private Syrus $view;

    /**
     * Render
     */
    public function render(string $html, StackElement $e):string
    {

        // Get usage
        if (!$usage = $e->getChildren('usage')) {
            throw new \Exception("No 's:usage' tag found within function documenation.");
        }
        $usage = $usage[0]->getBody();

        // Get description
        $description = '';
        if ($desc = $e->getChildren('desc')) {
            $description = $desc[0]->getBody();
        }

        // Get params
        $has_params=0;
        foreach ($e->getChildren('param') as $param) {
            $attr = $param->getAttrAll();
            if (!isset($attr['desc'])) {
                $attr['desc'] = $param->getBody();
            }

            $this->view->addBlock('params', [
                'name' => $attr['name'] ?? '',
                'required' => isset($attr['required']) && $attr['required'] == 1 ? 'Yes' : 'No',
                'type' => $attr['type'] ?? '',
                'description' => $attr['desc'] ?? ''
            ]);
            $has_params = 1;
        }

        // Check for return value
        list($has_return, $return) = [0, ''];
        if ($ret = $e->getChildren('return')) {
            $has_return = 1;
            $return = $ret[0]->getBody();
        }

        // Check for examples
        $has_examples = 0;
        foreach ($e->getChildren('example') as $ex) {

            $md = MarkdownExtra::defaultTransform("~~~php\n" . $ex->getBody() . "\n~~~\n");
            $md = preg_replace("/<code (.*?)>/", "<code class=\"prettyprint\">", $md);

            $this->view->addBlock('examples', [
                'title' => $ex->getAttr('title') ?? '',
                'code' => $md
            ]);
            $has_examples = 1;
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
        $this->view->assign('usage', $usage);
        $this->view->assign('description', $description);
        $this->view->assign('has_params', $has_params);
        $this->view->assign('has_return', $has_return);
        $this->view->assign('return', $return);
        $this->view->assign('has_examples', $has_examples);
        $this->view->assign('has_seealso', $has_seealso);

        // Parse html
        $html = file_get_contents(__DIR__ . '/../../themes/syrus/template_method.html');
        return $this->view->renderBlock($html);
    }

}



