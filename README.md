<h1 align="center">Multi-step Wizards for Laravel Nova 4</h1>

<p align="center">
Allows you to construct multi-step wizard forms, supports all native and custom Fields  in your Laravel Nova 4 application.</p>

![Wizard screenshot](https://github.com/wdelfuego/nova-wizard/blob/main/doc/screenshots/screenshot.jpg?raw=true)


# License summary
Anyone can use and modify this package in any way they want, including commercially, as long as the commercial use is a) creating implemented wizards and/or b) using the implemented wizards.

Basically, the only condition is that you can't sublicense the package or embed it in a framework unless you do so under the AGPLv3 license (which is incompatible with the Nova framework). More details [below](#license).

# Installation

1. Add the package to your project;
    ```sh
    composer require wdelfuego/nova-wizard
    ```

1. Publish the config file;
    ```sh
    php artisan vendor:publish --provider="Wdelfuego\NovaWizard\ToolServiceProvider" --tag="config"
    ```

1. Update the config file (in `config/nova-wizard.php`);

    The config file allows you to specify as many wizards as you want, each under their own key.

	- Update the class name to whatever class name you want to use for this wizard (we will create the class later)
    - Update the uri under which the wizard will be available to your end users
    - Optionally, update the window title for this wizard

    Here's an example minimal config for an `AddUserWizard`:

    ```php
    use App\Nova\Wizards\AddUserWizard;

    return [
        'add-user' => [
            'class' => AddUserWizard::class,
            'uri' => 'wizard/add-user',
            'windowTitle' => 'Add user'
        ]
    ];
    ```

	The key used for each entry in the config file is the 'wizard key' (here: `add-user`) and will be required to add the wizard views to your `NovaServiceProvider`.

1. Update your `NovaServiceProvider`;
	- Add the following statement to the top of the file:

		`use Wdelfuego\NovaWizard\NovaWizard;`

	- Add the wizard tool to the array returned by the `tools()` method, supplying the wizard key to its constructor:

		```php
		public function tools()
		{
			return [
				new NovaWizard('add-user')
			];
		}
		```

	- If you manually specify your application menu in the `boot()` method, add a `MenuSection` or `MenuItem` that links to this wizard.

        Specify the wizard key so the correct URL can be generated, like this:

		```php
		MenuItem::link('Add a user', NovaWizard::pathToWizard('add-user'))
		```


1. Finally, implement the wizard;
	- Create the class file for your wizard. It can go wherever you want, but `/app/Nova/Wizards` is a good default location if you have no preference.
    - Make sure you correctly specify that class for the wizard in `config/nova-wizard.php`
    - Implement the class to extend `Wdelfuego\NovaWizard\AbstractWizard`.
    - You only need to implement the following three methods:
        - `wizardViewData() : array`  to define the steps and fields in your wizard, like this:
            ```php
            use Laravel\Nova\Fields;

			public function wizardViewData() : array {
				return ['steps' => 
					[
						'title' => 'Step 1/2',
						'fields' => [
							// You can use all native Nova fields and custom fields that are available in your project
							Fields\Text::make(__('Username'), 'username'),
							Fields\Text::make(__('Text field'), 'myText'),
							Fields\Textarea::make(__('Longer text'), 'myLongerText')
	 							->help("You can use Help texts on Nova fields like you're used to"),
							Fields\Number::make(__('Some number'), 'myNumber')
								->rules('required')
								->withMeta(['value' => 60])
								->min(1)
								->step(1),
						]
					],
					[
						'title' => 'Step 2/2',
						'fields' => [
							Fields\Text::make(__('Text field 2'), 'myText2'),
							Fields\Textarea::make(__('Longer text 2'), 'myLongerText2')
	 							->help("You can use Help texts on Nova fields like you're used to"),
							Fields\Number::make(__('Some number 2'), 'myNumber2')
								->rules('required')
								->withMeta(['value' => 60])
								->min(1)
								->step(1),
						]
					],
				];
			}
    		```

            

        - `onSubmit($formData, &$context) : bool` to specify what to do when valid wizard data is submitted, like this:
            ```php
			public function onSubmit($formData, &$context) : bool
				//
				// When this method gets called, a valid and complete wizard was submitted.
				//
				// $formData is an array that contains the data submitted by the user.
				//
				// $context is an empty array that you can store arbitrary info in;
				// it will be passed to the next method so you can use it
				// to display specific context info to the user on success.
				
				// Parse submitted wizard data somehow
				$user = User::create(['name' => $formData['username']]);
				$context['newUserId'] = $user->id;

				// Return true at the end of this method to indicate success
				return true;

				// Or return false if the data can not be parsed successfully;
				// the user will then stay in the form view and have a chance
				// to revise the data before resubmitting.
			}
    		```

        - `successViewData($context) : array` to specify what message to show to the user when `onSubmit` returns true, like this:
            ```php
			public function successViewData($context) : array {
        
				return [
					'message' => 'Successfully created user with id: ' .$context['newUserId'];
				];
			}
    		```

# Adding more wizards

Repeat step 3 to 5 from the Installation steps above for every wizard you want to add to your Laravel Nova app.

# Support & Documentation

For any problems or doubts you might run into, please [open an issue](https://github.com/wdelfuego/nova-wizard/issues) on GitHub.


# License
Copyright © 2023 • Willem Vervuurt, Studio Delfuego, wdelfuego

This entire copyright and license notice must be included with any copy, back-up, 
fork or otherwise modified version of this package.

You can use this package under one of the follwing two licenses:

1. GNU AGPLv3 for GPLv3-or-newer compatible open source projects. Note that this license 
   is not compatible with usage in Nova, so this package can't be used under this license
   until a version exists that can be included in Laravel/Vue3 projects without 
   depending on Nova. You can find the full terms of this license in LICENSE-agpl-3.0.txt 
   in this repository and can also find a copy on https://www.gnu.org/licenses/.
    
2. A perpetual, non-revocable and 100% free (as in beer) do-what-you-want license 
   that allows both non-commercial and commercial use, under the following 6 conditions:
   
  - You can use this package to implement and/or use as many wizards in as many 
    applications on as many servers with as many users as you want and charge for 
    that what you want, as long as you and/or your organization are either
      a) the developer(s) responsible for implementing the wizard(s), or
      b) the end user(s) of the implemented wizard(s), or
      c) both.
    
  - Sublicensing, relicensing, reselling or charging for the redistribution of this 
    package (or a modified version of it) to other developers for them to implement 
    wizard views with is not allowed under this license.
    
  - You are free to make any modifications you want and are not required to make 
    your modifications public or announce them.
    
  - You are free to make and distribute modified versions of this package publicly 
    as long as you distribute it for free, as a stand-alone package and under the 
    same dual licensing model. 
    
  - Embedding this package (or a modified version of it) in free or paid-for software
    libraries or frameworks that are available to developers not within your 
    organization is expressly not allowed under this license. If the software library
    or framework is GPLv3-or-newer compatible, you are free to do so under the 
    GNU AGPLv3 license.
    
  - The following 2 disclaimers apply:

	  - THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
      IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
      FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL 
      THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
      LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
      OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN 
      THE SOFTWARE.
      
    - YOU ASSUME ALL RISK ASSOCIATED WITH THE INSTALLATION AND USE OF THE SOFTWARE. 
      LICENSE HOLDERS ARE SOLELY RESPONSIBLE FOR DETERMINING THE APPROPRIATENESS OF 
      USE AND ASSUME ALL RISKS ASSOCIATED WITH ITS USE, INCLUDING BUT NOT LIMITED TO
      THE RISKS OF PROGRAM ERRORS, DAMAGE TO EQUIPMENT, LOSS OF DATA OR SOFTWARE 
      PROGRAMS, OR UNAVAILABILITY OR INTERRUPTION OF OPERATIONS.

