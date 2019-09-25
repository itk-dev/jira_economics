@Library('jenkins-pipeline')_

pipeline {
    agent any
    stages {
        stage('Build and test') {
            parallel {
                stage('PHP') {
                    agent {
                        docker {
                            image 'itkdev/php7.2-fpm:latest' /* 7.2 is used as phan only runs with this version */
                            args '-v /var/lib/jenkins/.composer-cache:/.composer:rw'
                        }
                    }
                    stages {
                        stage('Build') {
                            steps {
                                sh 'composer install'
                            }
                        }
                        stage('PHP7 compatibility') {
                            steps {
                                sh 'vendor/bin/phan --allow-polyfill-parser'

                            }
                        }
                        stage('Coding standards') {
                            steps {
                                sh 'vendor/bin/phpcs --standard=phpcs.xml.dist'
                                sh 'vendor/bin/php-cs-fixer --config=.php_cs.dist fix --dry-run --verbose'
                                sh 'vendor/bin/twigcs lint templates'
                                sh 'vendor/bin/twigcs lint bundles/Billing/Resources/views'
                                sh 'vendor/bin/twigcs lint bundles/CreateProject/Resources/views'
                                sh 'vendor/bin/twigcs lint bundles/GraphicServiceOrder/Resources/views'
                            }
                        }
                    }
                }
                stage('Yarn - encore') {
                    stages {
                        stage('Install') {
                            steps {
                                sh 'docker run -v $WORKSPACE:/app -v /var/lib/jenkins/.yarn-cache:/usr/local/share/.cache/yarn:rw itkdev/yarn:latest install'
                            }
                        }
                        stage('Coding standards') {
                            steps {
                                sh 'docker run -v $WORKSPACE:/app -v /var/lib/jenkins/.yarn-cache:/usr/local/share/.cache/yarn:rw itkdev/yarn:latest check-coding-standards'
                            }
                        }
                        stage('Build') {
                            steps {
                                sh 'docker run -v $WORKSPACE:/app -v /var/lib/jenkins/.yarn-cache:/usr/local/share/.cache/yarn:rw itkdev/yarn:latest build'
                            }
                        }
                    }
                }
            }
        }
        stage('Deployment staging') {
            when {
                branch 'release'
            }
            steps {
                // Update git repos.
                sh "ansible srvitkphp72stg -m shell -a 'cd /data/www/economics_srvitkphp72stg_itkdev_dk/htdocs; git clean -d --force'"
                sh "ansible srvitkphp72stg -m shell -a 'cd /data/www/economics_srvitkphp72stg_itkdev_dk/htdocs; git checkout ${BRANCH_NAME}'"
                sh "ansible srvitkphp72stg -m shell -a 'cd /data/www/economics_srvitkphp72stg_itkdev_dk/htdocs; git fetch'"
                sh "ansible srvitkphp72stg -m shell -a 'cd /data/www/economics_srvitkphp72stg_itkdev_dk/htdocs; git reset origin/${BRANCH_NAME} --hard'"

                // Run composer.
                sh "ansible srvitkphp72stg -m shell -a 'cd /data/www/economics_srvitkphp72stg_itkdev_dk/htdocs; APP_ENV=prod composer install --no-dev -o'"

                // Run migrations.
                sh "ansible srvitkphp72stg -m shell -a 'cd /data/www/economics_srvitkphp72stg_itkdev_dk/htdocs; APP_ENV=prod php bin/console doctrine:migrations:migrate --no-interaction'"

                // Copy encore assets.
                sh "ansible srvitkphp72stg -m synchronize -a 'src=${WORKSPACE}/public/build/ dest=/data/www/economics_srvitkphp72stg_itkdev_dk/htdocs/public/build'"
            }
        }
        stage('Deployment production') {
            when {
                branch 'master'
            }
            steps {
                timeout(time: 30, unit: 'MINUTES') {
                    input 'Should the site be deployed?'
                }
                // Update git repos.
                sh "ansible srvitkeconomics -m shell -a 'cd /data/www/portal_itkdev_dk/htdocs; git clean -d --force'"
                sh "ansible srvitkeconomics -m shell -a 'cd /data/www/portal_itkdev_dk/htdocs; git checkout ${BRANCH_NAME}'"
                sh "ansible srvitkeconomics -m shell -a 'cd /data/www/portal_itkdev_dk/htdocs; git fetch'"
                sh "ansible srvitkeconomics -m shell -a 'cd /data/www/portal_itkdev_dk/htdocs; git reset origin/${BRANCH_NAME} --hard'"

                // Run composer.
                sh "ansible srvitkeconomics -m shell -a 'cd /data/www/portal_itkdev_dk/htdocs; composer install --no-dev -o'"

                // Copy encore assets.
                sh "ansible srvitkeconomics -m synchronize -a 'src=${WORKSPACE}/public/build/ dest=/data/www/portal_itkdev_dk/htdocs/public/build'"
            }
        }
    }
    post {
        always {
            script {
                slackNotifier(currentBuild.currentResult)
            }
        }
    }
}
