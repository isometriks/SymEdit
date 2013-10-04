<?php

namespace Isometriks\Bundle\SymEditBundle\Widget; 

use Symfony\Component\DependencyInjection\ContainerAware; 
use Symfony\Component\DependencyInjection\ContainerInterface; 
use Isometriks\Bundle\SymEditBundle\Model\WidgetInterface; 
use Isometriks\Bundle\SymEditBundle\Widget\Strategy\WidgetStrategyInterface; 

class WidgetRegistry extends ContainerAware
{
    private $strategies;
    private $templating;
    private $loadedStrategies; 
    
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param array $strategies
     */
    public function __construct(ContainerInterface $container, array $strategies)
    {
        $this->setContainer($container); 
        $this->strategies = $strategies; 
    }
    
    /**
     * @param string $name Strategy Name
     * @return Isometriks\Bundle\SymEditBundle\Widget\Strategy\WidgetStrategyInterface
     * @throws \Exception
     */
    public function getStrategy($name)
    {
        if(!isset($this->loadedStrategies[$name])){
            $this->loadStrategy($name); 
        }
        
        return $this->loadedStrategies[$name];  
    }
    
    private function loadStrategy($name)
    {
        /**
         * If we passed an alias then it can load quicker
         */
        if (isset($this->strategies[$name])) {
            $this->loadKey($name);
            
            return;
        }
        
        foreach($this->strategies as $key=>$id){
            $strategy = $this->loadKey($key);  
            
            if($strategy->getName() === $name){
                return; 
            }
        }
        
        throw new \Exception(sprintf('Could not find strategy "%s".', $name)); 
    }
    
    private function loadKey($key)
    {
        $id = $this->strategies[$key]; 
        $strategy = $this->container->get($id); 
        
        if(!$strategy instanceof WidgetStrategyInterface){
            throw new \Exception('Widgets must implement WidgetStrategyInterface'); 
        }
        
        $strategy->setTemplating($this->getTemplating());
        $this->loadedStrategies[$strategy->getName()] = $strategy; 
        
        /**
         * Check if keys/alias match. If not you should fix it
         */
        if (is_string($key) && $strategy->getName() !== $key) {
            throw new \Exception(sprintf('Widget tag alias (%s) does not match name (%s)', $key, $strategy->getName()));
        }
        
        unset($this->strategies[$key]); 
        
        return $strategy; 
    }
    
    private function getTemplating()
    {
        if ($this->templating === null) {
            $this->templating = $this->container->get('templating');
        }
        
        return $this->templating;
    }
    
    /**
     * 
     * @return WidgetStrategyInterface
     */
    public function getStrategies()
    {
        foreach($this->strategies as $key=>$id){
            $this->loadKey($key); 
        }
        
        return $this->loadedStrategies; 
    }
    
    public function injectStrategy(WidgetInterface $widget)
    {
        $strategy = $this->getStrategy($widget->getStrategyName()); 
        $widget->setStrategy($strategy); 
    }
    
    /**
     * Initiates the Widget with the strategy default options
     * 
     * @param \Isometriks\Bundle\SymEditBundle\Model\WidgetInterface $widget
     */
    public function init(WidgetInterface $widget)
    {
        $this->injectStrategy($widget); 
        $widget->getStrategy()->setDefaultOptions($widget); 
    }
}