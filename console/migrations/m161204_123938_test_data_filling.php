<?php

use common\models\Achievements;
use common\models\CommonInfo;
use common\models\Contacts;
use common\models\Projects;
use yii\db\Migration;

class m161204_123938_test_data_filling extends Migration
{
    /**
     *
     */
    public function safeUp()
    {

        $commonInfo = new CommonInfo();
        $commonInfo->info_header = 'about_header';
        $commonInfo->info_content = 'BRIEFLY ABOUT ME';
        $commonInfo->save();
        $commonInfo = new CommonInfo();
        $commonInfo->info_header = 'about_content_1';
        $commonInfo->info_content = 'Friends call me Dan. I am 20 years lod, now I studying on 3rd year of university in speciality
        "Computer science".';
        $commonInfo->save();
        $commonInfo = new CommonInfo();
        $commonInfo->info_header = 'about_content_2';
        $commonInfo->info_content = 'I\'m also like to get insight into web technologies, like JS with WebGL and AudioAPI, PHP with Yii2 and of course MySQL.
        Check out my github to see some of my projects';
        $commonInfo->save();
        $commonInfo = new CommonInfo();
        $commonInfo->info_header = 'about_github_link';
        $commonInfo->info_content = 'My GitHub: <a href="https://github.com/DenKorn">link</a>';
        $commonInfo->save();

        $commonInfo = new CommonInfo();
        $commonInfo->info_header = 'home_header';
        $commonInfo->info_content = 'WELCOME TO MY PORTFOLIO!';
        $commonInfo->save();
        $commonInfo = new CommonInfo();
        $commonInfo->info_header = 'home_body_content_1';
        $commonInfo->info_content = 'You are on page portfolio web developer! Perhaps you were looking for me, maybe not.';
        $commonInfo->save();
        $commonInfo = new CommonInfo();
        $commonInfo->info_header = 'home_body_content_2';
        $commonInfo->info_content = 'Anyway, I hope that you will like how this site and its contents! Have a nice day!';
        $commonInfo->save();

        $project = new Projects();
        $project->title = 'NATURALLY BIZARRE';
        $project->description = 'Collection of Experimants based on the series The Nature of Code by Daniel Shiffman. 
        Done via JavaScript and HTML5 Canvas.';
        $project->sort_order = 1;
        $project->complete_date = '2012-01-01';
        $project->link = '<a href="/den-portfolio-2/experiments/naturally-bizarre">ACCESS</a>';
        $project->save();

        $project = new Projects();
        $project->title = 'PARTICLES';
        $project->description = 'Implementation of Curl Noise in a Particles System. Done via JavaScript, OpenGL Shading Language and Web Graphics Library.';
        $project->sort_order = 2;
        $project->complete_date = '2015-06-25';
        $project->link = '<a href="/den-portfolio-2/experiments/particles">ACCESS</a>';
        $project->save();

        $project = new Projects();
        $project->title = 'CALL TO INNOVATION';
        $project->description = 'A challenge created by FIAP in partnership with Singularity University that
        proposes the use of technology to generate solutions to the humanity\'s greatest challenges.
        Done via HTML, CSS and JS, integrated with Wordpress.';
        $project->sort_order = 3;
        $project->complete_date = '2016-11-12';
        $project->link = '<a href="https://www.fiap.com.br/calltoinnovation/">ACCESS</a>';
        $project->save();

        ///////////////////////////////////////////////////

        $contact = new Contacts();
        $contact->title = 'Title';
        $contact->description = 'If you have something to say, find me via any link below:';
        $contact->save();

        $contact = new Contacts();
        $contact->title = 'VK';
        $contact->description = 'VK: <a href="https://vk.com/de_ko">ACCESS</a>';
        $contact->save();

        $contact = new Contacts();
        $contact->title = 'Facebook';
        $contact->description = 'Facebook: <a href="https://www.facebook.com/denis.kornejchuk">ACCESS</a>';
        $contact->save();

        $contact = new Contacts();
        $contact->title = 'Github';
        $contact->description = 'GitHub: <a href="https://github.com/DenKorn">ACCESS</a>';
        $contact->save();

        //////////////////////////////////////////////////////

        $achiv = new Achievements();
        $achiv->title = 'Regional programming contest lider';
        $achiv->content = 'It was funny and interesting algorithm contest, where I had taken second position. 
        You can laught at me, but I done it using Pascal! Oh, sad times of my past... Now, with JS, I can\'t even 
        imagine, how I used some bullshit like Pascal :)';
        $achiv->time = '2009-10-25';
        $achiv->save();

        $achiv = new Achievements();
        $achiv->title = 'STARTUP MEMBER';
        $achiv->content = 'Yes, it\'s damn right. I tried to change the world with my friend. 
        We failed it, but whatever it was nice experience to try again in near future.';
        $achiv->time = '2015-09-12';
        $achiv->save();

        $achiv = new Achievements();
        $achiv->title = 'JUST A COOL GUY';
        $achiv->content = 'Really! Just try to contact me in some way and become sure in this fact! 
        If you have eny proposes, we can deal about making some great project!';
        $achiv->time = '1945-9-2';
        $achiv->save();
    }

    public function safeDown()
    {
        $this->delete('achievements');
        $this->delete('contacts');
        $this->delete('projects');
        $this->delete('common_info');
        return true;
    }
}
