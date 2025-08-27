<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\ActivityLoggable;

class Theme extends Model
{
    use HasFactory, ActivityLoggable;

    protected $table = 'themes';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'name',
        'primary_color',
        'secondary_color',
        'accent_color',
        'background_color',
        'text_color',
        'heading_font',
        'body_font',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'is_active' => false,
        'primary_color' => '#4e73df',
        'secondary_color' => '#858796',
        'accent_color' => '#36b9cc',
        'background_color' => '#f8f9fc',
        'text_color' => '#333333',
        'heading_font' => 'Inter',
        'body_font' => 'Roboto'
    ];

    /**
     * Scope to get only active themes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }



    /**
     * Activate this theme and deactivate others
     */
    public function activate()
    {
        // Deactivate all other themes
        static::where('id', '!=', $this->id)->update(['is_active' => false]);
        
        // Activate this theme
        $this->update(['is_active' => true]);
        
        return $this;
    }

    /**
     * Get the currently active theme
     */
    public static function getActive()
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Check if this theme is the default theme
     */
    public function isDefault()
    {
        return $this->name === 'Default Theme';
    }

    /**
     * Get available font options
     */
    public static function getAvailableFonts()
    {
        return [
            'Inter',
            'Roboto',
            'Open Sans',
            'Lato',
            'Montserrat',
            'Source Sans Pro',
            'Raleway',
            'Ubuntu',
            'Nunito',
            'Poppins',
            'Playfair Display',
            'Merriweather',
            'PT Sans',
            'Oswald',
            'Crimson Text'
        ];
    }

    /**
     * Get CSS variables for this theme
     */
    public function getCssVariables()
    {
        return [
            '--primary-color' => $this->primary_color,
            '--secondary-color' => $this->secondary_color,
            '--accent-color' => $this->accent_color,
            '--background-color' => $this->background_color,
            '--text-color' => $this->text_color,
            '--heading-font' => "'{$this->heading_font}', sans-serif",
            '--body-font' => "'{$this->body_font}', sans-serif"
        ];
    }

    /**
     * Generate CSS string for this theme
     */
    public function toCss()
    {
        $variables = $this->getCssVariables();
        $css = ":root {\n";
        
        foreach ($variables as $property => $value) {
            $css .= "    {$property}: {$value};\n";
        }
        
        $css .= "}";
        
        return $css;
    }
}