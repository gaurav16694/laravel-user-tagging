# End-to-end Mentions in Laravel 5

[![Build Status](https://travis-ci.org/jameslkingsley/laravel-mentions.svg?branch=master)](https://travis-ci.org/jameslkingsley/laravel-mentions)

This Laravel >=5.4 package provides an easy way to setup mentions for Eloquent models. It provides the front-end for inserting mentions into **content-editable** elements, the back-end for associating mentions with models and lastly an elegant way to notify the mentioned models that they have been mentioned.

Here are a few short examples of what you can do:

```php
// Mention a single user
$comment->mention($user);

// Mention a collection of users
$comment->mention($users);

// Handle the form data
$comment->mention($request->mentions);

// Get all mentions, resolved to their models
$comment->mentions();

// Unmention a single user
$comment->unmention($user);

// Unmention a collection of users
$comment->unmention($users);
```

It handles notifications for you as well. If your mentions config has `auto_notify` enabled, it will do it for you. If you need to handle the logic yourself, disable `auto_notify` and explicitly notify the mention, for example:

```php
$mention = $comment->mention($user);
$mention->notify();
```

## Requirements

- [Tribute](https://github.com/zurb/tribute)

## Installation

You can install this package via composer using this command:

```bash
composer require jameslkingsley/laravel-mentions
```

**If you're using Laravel 5.5 or greater this package will be auto-discovered, however if you're using anything lower than 5.5 you will need to register it the old way:**

Next, you must install the service provider in `config/app.php`:

```php
'providers' => [
    ...
    gaurav\tagging\MentionServiceProvider::class,
];
```

Now publish the migration, front-end assets and config:

```bash
php artisan vendor:publish --provider="gaurav\tagging\MentionServiceProvider"
```

After the migration has been published you can create the mentions table by running the migrations:

```bash
php artisan migrate
```

This is the contents of the published config file:

```php
return [
    // Pools are what you reference on the front-end
    // They contain the model that will be mentioned
    'pools' => [
        'users' => [
            // Model that will be mentioned
            'model' => 'App\User',

            // The column that will be used to search the model
            'column' => 'name',

            // Notification class to use when this model is mentioned
            'notification' => 'App\Notifications\UserMentioned',

            // Automatically notify upon mentions
            'auto_notify' => true
        ]
    ]
];
```

## Usage

First you will need to import [Tribute](https://github.com/zurb/tribute).

```
npm install tributejs --save-dev
```

Then include Tribute in your `bootstrap.js` file and assign it globally.

```js
import Tribute from "tributejs";
window.Tribute = Tribute;
```

Now in your `bootstrap.js` file you can import the `Mentions` class and also assign it globally.

```js
import Mentions from './laravel-mentions';
window.Mentions = Mentions;
```

Now to include the styling just import it into your SCSS file.

```css
@import "laravel-mentions";
```

Now let's setup the form where we'll write a comment that has mentions:

```html
<form method="post" action="{{ route('comments.store') }}">
    <!-- This field is required, it stores the mention data -->
    <input type="hidden" name="mentions" id="mentions">

    <!-- We write the comment in the div -->
    <!-- The for attribute is a helper to auto-populate the textarea -->
    <textarea class="hide" name="text" id="text"></textarea>
    <div class="has-mentions" contenteditable="true" for="#text"></div>

    <button type="submit">
        Post Comment
    </button>

    <!-- CSRF field for Laravel -->
    {{ csrf_field() }}
</form>
```

Next add the script to initialize the mentions:

```js
new Mentions({
    // Input element selector
    // Defaults to .has-mentions
    input: '.has-mentions',

    // Output form field selector
    // Defaults to #mentions
    output: '#mentions',

    // Pools
    pools: [{
        // Trigger the popup on the @ symbol
        // Defaults to @
        trigger: '@',

        // Pool name from the mentions config
        pool: 'users',

        // Same value as the pool's 'column' value
        display: 'name',

        // The model's primary key field name
        reference: 'id'
    }]
});
```

Now onto the back-end. Choose the model that you want to assign mentions to. In this example I'll choose `Comments`. We'll import the trait and use it in the class.

```php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Kingsley\Mentions\Traits\HasMentions;

class Comment extends Model
{
    use HasMentions;
}
```

Next switch to the controller for where you store the comment. In this case it's `CommentController`. Create the comment however you like, and afterwards just call the `mention` method.

```php
public function store(Request $request)
{
    // Handle the comment creation however you like
    $comment = Comment::create($request->all());

    // Call the mention method with the form mentions data
    $comment->mention($request->mentions);
}
```

That's it! Now when displaying your comments you can style the `.mention-node` class that is inserted via Tribute. That same node also has a `data-object` attribute that contains the pool name and reference value, eg: `users:1`.

### Editing Content With Mentions
You'll most likely need to edit the text content, so it's necessary to restore the mentions list in the form.
It's as simple as this:

```html
<input type="hidden" name="mentions" id="mentions" value="{{ $comment->mentions()->encoded() }}">
```

### Notifications

If you want to use notifications, here's some stuff you may need to know.

- When a mention is notified, it will use Laravel's built-in Notification trait to make the notification. That means the model class defined in the pool's config must have the `Notifiable` trait.
- It will use the notification class defined in the pool's config, so you can handle it differently for each one.
- The data stored in the notification will always be the model that did the mention, for example `$comment->mention($user)` will store `$comment` in the data field.
