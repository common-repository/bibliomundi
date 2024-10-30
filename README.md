# Sobre o Plugin

Nós somos a <a href="http://www.bibliomundi.com.br" target="blank">Bibliomundi</a>, uma distribuidora de livros digitais e disponibilizamos este Plugin, para o Woocommerce, com o objetivo de integrar os ebooks cadastrados em nossa plataforma com a sua loja. Para que você possa vender nossos ebooks em sua loja é muito simples e não necessita conhecimentos em programação.

#Versão

1.0

#Requerimentos

<a href="https://woocommerce.com/" target="blank">Woocommerce</a> na versão 2.4 ou maior.

<a href="http://php.net" target="blank">PHP</a> na versão 5.4 ou maior.

Extensões <a href="http://php.net/manual/pt_BR/book.mcrypt.php" target="blank">mcrypt</a> e <a href="http://php.net/manual/pt_BR/book.curl.php" target="blank">cURL</a> do PHP

#Instalação

Baixe o nosso módulo em <a target="blank" href="https://drive.google.com/file/d/0BzwFNhJ9FBNwWkNJaHBOQU4wWmc/view?usp=sharing">https://drive.google.com/file/d/0BzwFNhJ9FBNwWkNJaHBOQU4wWmc/view?usp=sharing</a>. No Wordpress, navegue até a aba de plugins. Clique em adicionar novo e faça o upload do zip. Após fazer o upload, clique em ativar plugin. Pronto, nosso plugin deverá estar aparecendo em sua lista.

Obs. Caso esteja tendo dificuldades na instalação, configuração ou importação dos ebooks, disponibilizamos um tutorial com ilustrações. 
Você pode visualizar <a target="blank" href="https://docs.google.com/document/d/1VGHVvO8zuflDOm8u_FfnGw6lRpbnnpzo9fIgfRLgQM8/edit?usp=sharing">aqui</a>.

#Configurando o Módulo

Clique na aba do Woocommerce > configurações e em seguida clique na aba bibliomundi.

#Importando os Ebooks

Esse é o momento em que você irá importar os ebooks cadastrados em nossa plataforma para a sua loja. 
Clique na aba do Woocommerce > configurações e em seguida clique na aba bibliomundi.
Você precisa apenas Informar a chave e a senha que enviamos para você, escolha o ambiente e ação desejada e clique em importar. 
Atenção. O tempo da importação irá variar de acordo com vários fatores, tais como a  velocidade do seu servidor e da conexão de sua internet!

#Atualizações Diárias

Realizamos atualizações diárias em nosso sistema e você precisará, também diariamente, criar uma rotina para checar se existem ebooks a serem inseridos, atualizados ou deletados.
Recomendamos que crie uma agendador de tarefas para rodar entre 01 e 06 da manhã(GMT-3) afim de evitar que ebooks sejam disponibilizados com dados defasados podendo assim causar erros na venda.
Tudo o que você precisa fazer é executar, periodicamente, o arquivo "cron.php" que se encontra na raiz do diretório do plugin.

Atenção. Esta etapa requer conhecimentos de infra-estrutura. Sugerimos que contacte o administrador do servidor. 

Você não irá conseguir fazer a chamada via url, como por exemplo "http:www//seuwoocommerce/wp-content/plugins/Plugin-Bibliomundi-Woocommerce/cron.php",
pois o prestashop requer um token de autenticação e o mesmo é dinâmico, portanto você deverá executar o arquivo via linha de comando. Ex: "php /home/USER/public_html/wp-content/plugins/Plugin-Bibliomundi-Woocommerce/cron.php"

#Observações

- Dependendo das configurações de seu servidor é possível que ocorra timeout quando importando o nosso catálogo. Se isso acontecer simplesmente refaça o processo até que todos os ebooks tenham sido importados.(Isso também serve para as atualizações diárias).
- Após desinstalar o nosso plugin, todos os nossos ebooks serão removidos de sua lista de produtos, bem como suas respectivas categorias, características e etiquetas e isso também pode demorar vários minutos.
- Execute as atualizações entre 01 e 06 da manhã(GMT-3).
