<?php

namespace App\Filament\Admin\Pages;

use App\Models\Setting;
use App\Support\Delivery;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.admin.pages.manage-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Site Settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_tagline' => Setting::get('site_tagline', "Let your skin do the talking"),
            'homepage_hero_heading' => Setting::get('homepage_hero_heading'),
            'homepage_hero_subheading' => Setting::get('homepage_hero_subheading'),
            'homepage_hero_image' => Setting::get('homepage_hero_image'),
            'about_founder_name' => Setting::get('about_founder_name', 'Abibat Otaobayomi Cardoso'),
            'about_founder_photo' => Setting::get('about_founder_photo', 'images/founder.jpeg'),
            'about_founder_body' => Setting::get('about_founder_body'),
            'contact_email' => Setting::get('contact_email'),
            'contact_phone' => Setting::get('contact_phone'),
            // Delivery::pickupAddress() rather than Setting::get(), so the box
            // shows the address customers are actually being given — including
            // the built-in default, before this has ever been saved.
            'pickup_address' => Delivery::pickupAddress(),
            'order_notification_email' => Setting::get('order_notification_email'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Homepage')
                    ->components([
                        TextInput::make('site_tagline'),
                        TextInput::make('homepage_hero_heading'),
                        TextInput::make('homepage_hero_subheading'),
                        FileUpload::make('homepage_hero_image')
                            ->image()
                            ->disk('public')
                            ->directory('cms')
                            ->visibility('public'),
                    ]),
                Section::make('About Us — Meet the Founder')
                    ->components([
                        TextInput::make('about_founder_name'),
                        FileUpload::make('about_founder_photo')
                            ->image()
                            ->disk('public')
                            ->directory('cms')
                            ->visibility('public'),
                        RichEditor::make('about_founder_body')
                            ->columnSpanFull(),
                    ]),
                Section::make('Contact details')
                    ->columns(2)
                    ->components([
                        TextInput::make('contact_email')
                            ->label('Public contact email')
                            ->helperText('Shown to customers on the contact and policy pages.')
                            ->email(),
                        TextInput::make('contact_phone'),
                    ]),
                Section::make('Collection')
                    ->description('Where customers who choose "Collect in person" come to pick their order up.')
                    ->components([
                        TextInput::make('pickup_address')
                            ->label('Collection address')
                            ->helperText('Shown at checkout, on the Delivery page and in the confirmation email. Clearing it restores the built-in address rather than showing nothing.')
                            ->placeholder(Delivery::pickupAddress())
                            ->maxLength(255),
                    ]),
                Section::make('Order notifications')
                    ->description('Where to tell you a new order has come in.')
                    ->components([
                        TextInput::make('order_notification_email')
                            ->label('Send new order alerts to')
                            ->helperText('You get an email here every time an order is paid for. Leave blank to turn the alerts off.')
                            ->email()
                            ->placeholder('you@example.com'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
