# Enable approval on model create, update, and delete

This package will make a model changes to pending state whenever a create, update, or delete action is executed. A user model or any model can then approve it or reject it.

Once installed you can do stuff like this:

```php

// whenever a model is created, updated, or deleted, such as this:
$model->save();
// will generate an approval request to be approved or rejected

// and then a user can respond to it by
$user->respondApproval($model, ApprovalResponse::STATUS_APPROVE);

```

## Features

- Any model using HasApproval trait can approve or reject
- Multi level approval
- Multi approver

## Features to be developed

- Artisan commands to create policies, approvers data
- A better implementation for approval policies
- Custom approval types other than create, update, and delete, e.g, when a certain data field change to something

## Need a UI?

The package doesn't come with any UI, you should build that yourself. But you can contact me if you want to implement it.

## Contact & Support

I'm a web developer from Indonesia. I offer services on web development, especially using Laravel. Please email me at [basugi99@gmail.com](mailto:basugi99@gmail.com).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
