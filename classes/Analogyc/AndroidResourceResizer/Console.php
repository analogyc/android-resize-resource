<?php

namespace Analogyc\AndroidResourceResizer;

use Imagine\Image\Box;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class Console extends Command
{
	/**
	 * @var string
	 */
	protected $source_dir = '';

	/**
	 * @var string
	 */
	protected $dest_dir = '';

	/**
	 * @var string[]
	 */
	protected $resolutions = array();

	/**
	 * @var string
	 */
	protected $base_resolution = '';

	/**
	 * Configure the command line interface arguments
	 */
	protected function configure()
	{
		$this
			->setName('resize')
			->setDescription('Resize resource files')
			->addOption(
				'source-dir',
				null,
				InputOption::VALUE_REQUIRED,
				'The directory where the media files are found'
			)
			->addOption(
				'dest-dir',
				null,
				InputOption::VALUE_REQUIRED,
				'The directory where the media files are to be saved (the res directory)'
			)
			->addOption(
				'resolutions',
				null,
				InputOption::VALUE_REQUIRED,
				'The Android resolutions to support',
				'ldpi|mdpi|hdpi|xhdpi|xxhdpi|xxxhdpi'
			)
			->addOption(
				'base-resolution',
				null,
				InputOption::VALUE_REQUIRED,
				'The highest target resolution',
				'xxxhdpi'
			);
	}

	/**
	 * Manage the command line request
	 *
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->source_dir = rtrim($input->getOption('source-dir'), '/').'/';
		$this->dest_dir = rtrim($input->getOption('dest-dir'), '/').'/';
		$this->resolutions = explode('|', $input->getOption('resolutions'));
		$this->base_resolution = $input->getOption('base-resolution');

		$this->resize();
	}

	protected function resize()
	{
		// in this version we only use Imagick
		$imagine = new \Imagine\Imagick\Imagine();

		$finder = new Finder();
		$finder->files()->name('/(\.png|\.jpeg|\.jpg)$/')->in($this->source_dir);
		foreach ($finder as $file)
		{
			list($original_width, $original_height) = getimagesize($file->getRealpath());

			foreach ($this->resolutions as $res)
			{
				$sizes = $this->getSize($original_width,$original_height, $res, $this->base_resolution);

				$dest_dir = $this->dest_dir.'drawable-'.$res.'/'.$file->getRelativePath();
				if (!file_exists($dest_dir))
				{
					mkdir($dest_dir);
				}

				$imagine
					->open($file->getRealpath())
					->resize(new Box($sizes[0], $sizes[1]))
					->save(realpath($dest_dir).'/'.$file->getFilename());
			}
		}
	}

	protected function getSize($original_width, $original_height, $density, $original_density)
	{
		/*
		 * We must start off these basic values that tell the size of the icon
		 *
		 *  "To create alternative bitmap drawables for different densities,
		 *  you should follow the 3:4:6:8 scaling ratio between the four generalized densities.
		 *  For example, if you have a bitmap drawable that's 48x48 pixels for medium-density screen
		 *  (the size for a launcher icon), all the different sizes should be:"
		 *
		 * drawable-ldpi (120 dpi, Low density screen) - 36px x 36px
		 * drawable-mdpi (160 dpi, Medium density screen) - 48px x 48px
		 * drawable-hdpi (240 dpi, High density screen) - 72px x 72px
		 * drawable-xhdpi (320 dpi, Extra-high density screen) - 96px x 96px
		 * drawable-xxhdpi (480 dpi, Extra-extra-high density screen) - 144px x 144px
		 * drawable-xxxhdpi (640 dpi, Extra-extra-extra-high density screen) - 192px x 192px
		 *
		 * Thanks, Google. Srsly.
		 *
		 * 144 / 36 = 4
		 * 144 / 48 = 3
		 * 144 / 72 = 2
		 * 144 / 96 = 1,5
		 * 144 / 144 = your mom
		 * 144 / 192 = 0,75
		 */

		$resolutions = array(
			'ldpi' => 36,
			'mdpi' => 48,
			'hdpi' => 72,
			'xhdpi' => 96,
			'xxhdpi' => 144,
			'xxxhdpi' => 192
		);

		if (!isset($resolutions[$original_density]))
		{
			throw new Exception('No such screen density available: '.$original_density);
		}

		if (!isset($resolutions[$density]))
		{
			throw new Exception('No such screen density available: '.$density);
		}

		$density_divider = $resolutions[$original_density] / $resolutions[$density];

		return array(
			round($original_width / $density_divider),
			round($original_height / $density_divider)
		);
	}
}