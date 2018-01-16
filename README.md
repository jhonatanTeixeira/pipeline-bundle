# Pipeline Bundle

## Configuração

```yaml
vox_pipeline:                     # nome do bundle
    pipelines:                    # deve declarar um array de pipelines
      primeiro_passo_filter:      # será o nome do serviço que poderar ser referenciado na injeção de dependencia
          type: kernel-subscriber # tipo de serviço, pode ser kernel-subscriber, doctrine-subscriber e service
          subscribedEvents:       # eventos que os subscribers irão escutar
            - api.form.post_build.primeiroPasso
          services:               # serviços que compõem a pipeline, srão executados na ordem de declaração
            - AppBundle\Pipeline\Form\DadosProcessoPipelineService
            - AppBundle\Pipeline\Form\EmpresaPipelineService
            - AppBundle\Pipeline\Form\PassoDois\Empresario\EmpresarioPipelineService
```

## Declaração de um item da pipeline

Todos os items da pipeline devem ser "callables" que recebem um objeto de contexto

```php
class PipelineItem
{
    /**
     * se houver esse método declarado, ele será chamado antes de chamar o __invoke 
     * e caso ele retone false, não será chamado o __invoke
     * essa interface é implicita, não sendo necessário implementar nenhuma interface
     */
    public function shouldCall(PipelineContext $context): boolean
    {
        return true;
    }
    
    public function __invoke(PipelineContext $context)
    {
        //code
    }
}
```

## Tipos de pipelines

### - Kernel Subscriber (kernel-subscriber):

Escuta a qualquer evento disparado pelo EventDispatcher do symfony, recebera um objeto do tipo PipelineContext com o evento real dentro da propriedade event que pode ser pego da seguintes formas:

```php
public function __invoke(PipelineContext $context)
{
    $event = $context->event;
    $event = $context->get('event');
}
```

### - Doctrine Subscriber (doctrine-subscriber)
Escuta a qualquer evento do doctrine, e recebe o evento da mesma forma descrita para o kernel subscriber.
na configuração, deve se usar no subscribedEvents os nomes dos eventos doctrine. Ex:

```yaml
subscribedEvents:
    - prePersist
    - postPersist
    - preUpdtade
    - postUpdate
    - onFlush
```

### - Service (service)
Esse tipo não escuta a nada, apenas vira um serviço para ser injetado em outros serviço, e se trata de um objeto do tipo PiplineRunner. Eis um exemplo de uso da pipeline:

config so serviço:

```yaml
vox_pipeline:
    pipelines:
        primeiro_passo_filter:
            type: service
            services:
                - AppBundle\Pipeline\Form\DadosProcessoPipelineService
                - AppBundle\Pipeline\Form\EmpresaPipelineService
                - AppBundle\Pipeline\Form\PassoDois\Empresario\EmpresarioPipelineService
                
services:
    AppBundle\Service\SomeService:
        arguments: ['@primeiro_passo_filter']
```

```php
class SomeService
{
    private $pipelineRunner;
    
    public function __contruct(PipelineRunner $pipelineRunner)
    {
        $this->pipelineRunner = $pipelineRunner;
    }
    
    public function soSomething()
    {
        $context = new PipelineContext();
        
        $this->pipelineRunner->run($context);
        
        // o pipeline runner é também um callable, pode ser executado como uma função
        call_user_func($this->pipelineRunner, $context);
        
        // ou
        $this->pipelineRunner($context);
    }
}
```

## Controle de fluxo

Existem algumas maneiras de controllar o fluxo da pipeline

Através de exceptions:

```php
public function __invoke(PipelineContext $context)
{
    //para a execução dessa pipe e vai para a proxima
    throw new CannotHandleContextException();
}
```

```php
public function __invoke(PipelineContext $context)
{
    //para a execução dessa pipe e termina a execução, evitando de chamar que chame as proximas
    throw new ShouldStopPropagationException();
}
```

```php
public function __invoke(PipelineContext $context)
{
    // se esse metodo for chamado, os proximos itens da pipeline não serão mais chamados
    // a execução desse item continua até o final
    $context->stopPropagation();
}
```