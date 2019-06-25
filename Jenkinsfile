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
                        stage('Build') {
                            steps {
                                sh 'docker run -v $WORKSPACE:/app -v /var/lib/jenkins/.yarn-cache:/usr/local/share/.cache/yarn:rw itkdev/yarn:latest encore production'
                            }
                        }
                    }
                }
            }
        }
        stage('Deployment develop') {
            when {
                branch 'develop'
            }
            steps {
                // Update git repos.
                sh "ansible srvitkphp72stg -m shell -a 'cd /home/deploy/www/economics_srvitkphp72stg_itkdev_dk/htdocs; git clean -d --force'"
                sh "ansible srvitkphp72stg -m shell -a 'cd /home/deploy/www/economics_srvitkphp72stg_itkdev_dk/htdocs; git checkout ${BRANCH_NAME}'"
                sh "ansible srvitkphp72stg -m shell -a 'cd /home/deploy/www/economics_srvitkphp72stg_itkdev_dk/htdocs; git fetch'"
                sh "ansible srvitkphp72stg -m shell -a 'cd /home/deploy/www/economics_srvitkphp72stg_itkdev_dk/htdocs; git reset origin/${BRANCH_NAME} --hard'"

                // Run composer.
                sh "ansible srvitkphp72stg -m shell -a 'cd /home/deploy/www/economics_srvitkphp72stg_itkdev_dk/htdocs; composer install --no-dev -o'"

                // Copy encore assets.
                sh "ansible srvitkphp72stg -m synchronize -a 'src=${WORKSPACE}/public/build/ dest=/home/deploy/www/economics_srvitkphp72stg_itkdev_dk/htdocs/public/build'"
            }
        }
        stage('Deployment staging') {
            when {
                branch 'release'
            }
            steps {
                sh 'echo "DEPLOY"'
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
                sh "ansible srvitkphp72 -m shell -a 'cd /home/deploy/www/b7_itkdev_dk/htdocs; git clean -d --force'"
                
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