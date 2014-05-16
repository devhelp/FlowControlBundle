[![Build Status](https://travis-ci.org/devhelp/FlowControlBundle.png)](https://travis-ci.org/devhelp/FlowControlBundle) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/cf024010-1a11-47c8-988a-bb0b8e2aac56/mini.png)](https://insight.sensiolabs.com/projects/1dc1cc7b-a147-4202-8bb7-0768ec6d82a3)

## Installation

Composer is preferred way to install FlowControlBundle, please check [composer website](http://getcomposer.org) for more information.

```
$ composer require 'devhelp/flow-control-bundle:dev-master'
```

## Purpose

FlowControlBundle provider integration with [FlowControl library](https://github.com/devhelp/flow-control) regarding controller actions.
It allows you to define your actions as flow steps, meaning that user won't be able to enter an action unless he/she reaches expected step
in your flow.

You can, for example:
- define checkout flow for your online store
- define multiple flows at once for different use cases

## Usage

### Define your flow in config.yml

```
devhelp_flow_control:
    flows:
        #your flow name
        my_checkout_flow:
            moves:
                order_configuration:  [order_summary]
                order_summary:        [order_configuration, payment]
                payment:              [order_configuration, order_summary, payment_summary]
            entry_points:             [order_configuration]
        my_simple_checkout_flow:
            moves:
                buy_now:              [order_summary]
                order_summary:        [payment]
                payment:              [payment_summary]
            entry_points:             [buy_now]
        #multiple flows can be defined
```

### Define controller actions as flow steps

```
use Devhelp\FlowControlBundle\FlowConfiguration\Annotation\Flow;

class OrderController
{
   /**
     * @Flow(name="my_simple_checkout_flow", step="buy_now")
     */
    public function buyNowAction()
    {
        //...
    }

    /**
     * @Flow(name="my_checkout_flow", step="order_configuration")
     */
    public function configureAction()
    {
        //...
    }
    
    /**
     * @Flow(name="my_checkout_flow", step="order_summary")
     * @Flow(name="my_simple_checkout_flow", step="order_summary")
     */
    public function summaryAction()
    {
        //...
    }
}
```

```
use Devhelp\FlowControlBundle\FlowConfiguration\Annotation\Flow;

class PaymentController
{
    /**
     * @Flow(name="my_checkout_flow", step="payment")
     * @Flow(name="my_simple_checkout_flow", step="payment")
     */
    public function paymentAction()
    {
        //...
    }
    
    /**
     * @Flow(name="my_checkout_flow", step="payment_summary")
     * @Flow(name="my_simple_checkout_flow", step="payment_summary")
     */
    public function summaryAction()
    {
        //...
    }
}

```

### Done!

Now if you enter an url for action that is some flow step you're access will be restricted only if your move is valid


## FAQ

### How does it change the steps in flow ?

Every action that ends with successful or redirect response is treated as success and ending such action will result
in changing current step in the flow for every valid move

### Where are the current steps stored ?

'devhelp.flow_control.current_steps' service is responsible for it. Default implementation stores current steps in session

### How can I change the way current steps are stored

Right now it is not configurable from the bundle itself (will be soon), but you can use CompilerPass to replace 
'devhelp.flow_control.current_steps' service with custom implementation

### I don't want my action to update current steps automatically

You can disable this in action configuration
```
use Devhelp\FlowControlBundle\FlowConfiguration\Annotation\Flow;
use Devhelp\FlowControlBundle\FlowConfiguration\Annotation\DisableAutocommit;

class ExampleController
{
    /**
     * @Flow(name="my_flow", action="my_step")
     * @DisableAutocommit
     */
    public function fooAction()
    {
        //...
    }
}

```

### How can I customize what happens once no valid steps are found for the action ?

Currently suggested way is to intercept NoValidStepsFoundException on 'kernel.exception' event and replace the Response


## Credits

Brought to you by : Devhelp.pl (http://devhelp.pl)
