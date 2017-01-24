blocks-exam_actions
===================

Bloco que apresenta um conjunto de ações relativas à realização de provas.

Moodle Provas
=============

O "Moodle Provas" é uma solução desenvolvida pela
Universidade Federal de Santa Catarina
com financiamenteo do programa Universidade Aberta do Brasil (UAB)
para a realização de provas seguras nos pólos utilizando
o Moodle através da internet.

Além deste plugin, mais dois plugins compõem o pacote do Moodle Provas:

* local-exam_remote: Plugin que cria os webservices necessários no Moodle de origem
* local-exam_authorization : Bloco que trata da autorização de usuários ao ambiente de provas

Foi desenvolvido também um "CD de Provas", derivado do Ubuntu, para
restringir o acesso aos recursos dos computadores utilizados
para realização da provas.

No endereço abaixo você pode acessar um tutorial sobre a
arquitetura do Moodle Provas:

    https://github.com/UFSC/moodle-provas-blocks-exam_actions/wikis/home

Download
========

Este plugin está disponível no seguinte endereço:

    https://github.com/UFSC/moodle-provas-blocks-exam_actions

Os outros plugins podem ser encontrados em:

    https://github.com/UFSC/moodle-provas-local-exam_authorization
    https://github.com/UFSC/moodle-provas-local-exam_remote

O código e instruções para gravação do "CD de Provas" podem ser encontrados em:

    https://github.com/UFSC/moodle-provas-livecd-provas

Instalação
==========

* Este plugin deve ser instalado no "Moodle de Provas".
* Este plugin é do tipo bloco, logo deve ser instalado no diretório "blocks", na raiz do seu moodle.
* O nome diretório deste plugin dentro do diretório "blocks" deve ser "exam_actions" (sem as aspas).
* Após colocar o código do plugin no diretório correto, visite o seu Moodle como administrador para finalizar a instalação.

Pós-instalação
==============

Após instalar o módulo, execute as seguinte ações:

* Incluir instância do bloco "Moodle Provas" na "Página inicial do site" (necessário para que esteja disponível a ação de "Liberar computador para realizar prova)"
* Incluir instância do bloco intitulado "Cursos" na página inicial do usuário (Minha página inicial padrão) para que ao entrar o usuário veja a lista de cursos nos quais ele está inscrito
* Incluir no arquivo config.php a linha:
 * $CFG->defaultblocks_override = ':exam_actions';
 * Para que seja automaticamente adicionado este bloco em cada curso Moodle que seja criado.
 * Necessário para que estejam disponíveis diversas ações em nível de curso
* Ajustar as configurações do módulo local-exam_authorization, em particular as instalações remotas de Moodle integradas ao Moodle Provas
* Alterar configurações do Moodle Provas de forma a ajustá-lo às necessidades da instituição

Licença
=======

Este código-fonte é distribuído sob licença GNU General Plublic License
Uma cópia desta licença está no arquivo COPYING.txt
Ela também pode ser vista em <http://www.gnu.org/licenses/>.
