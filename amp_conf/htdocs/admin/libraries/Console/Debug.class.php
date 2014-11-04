<?php
namespace FreePBX\Console\Command;
//Symfony stuff all needed add these
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
//For running system commands.
use Symfony\Component\Process\Process;
//Tables
use Symfony\Component\Console\Helper\Table;
//Kill output buffering 
ini_set('output_buffering',0);

class Debug extends Command {
	protected function configure(){
		$this->FreePBXConf = \FreePBX::Config();
		$this->Notifications = \FreePBX::Notifications();
		$this->setName('dbug')
		->setAliases(array('debug'))
		->setDescription('Stream files for debugging')
		->setDefinition(array(
			new InputArgument('args', InputArgument::IS_ARRAY, null, null),));
	}
	protected function execute(InputInterface $input, OutputInterface $output){
		$this->FreePBXConf->set_conf_values(array('FPBXDBUGDISABLE' => 0),true,true);
		$DBUGFILE = $this->FreePBXConf->get('FPBXDBUGFILE',true);
		$user = $this->FreePBXConf->get('AMPASTERISKWEBUSER',true);
		$group = $this->FreePBXConf->get('AMPASTERISKWEBGROUP',true);
		touch($DBUGFILE);
		chown($DBUGFILE, $user);
		chgrp($DEBUGFILE, $group);
		$files = array(
			$DBUGFILE,
			'/var/log/httpd/access_log',
			'/var/log/httpd/error_log',			
			);
		$table = new Table($output);
		$table->setHeaders(array('FreePBX Notifications'));
		$table->render();
		unset($table);
		foreach($this->Notifications->list_all() as $notice){
				if($notice['extended_text'] != strip_tags($notice['extended_text'])) {
					//breaks in the console make me sad. 
					$longtext = preg_replace('#<br\s*/?>#i', PHP_EOL, $notice['extended_text']);
					$output->write($longtext);
				}else{
					$output->writeln('');
					$output->writeln($notice['extended_text']);
				}
		}
		$files = implode(' ', $files);
		passthru('tail -f ' . $files);
	}
}
