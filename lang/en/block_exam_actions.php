<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
//
// Este bloco é parte do Moodle Provas - http://tutoriais.moodle.ufsc.br/provas/
// Este projeto é financiado pela
// UAB - Universidade Aberta do Brasil (http://www.uab.capes.gov.br/)
// e é distribuído sob os termos da "GNU General Public License",
// como publicada pela "Free Software Foundation".

/**
 * Language file for block "Exam Actions".
 *
 * @package    block_exam_actions
 * @author     Antonio Carlos Mariani
 * @copyright  2010 onwards Universidade Federal de Santa Catarina (http://www.ufsc.br)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Ações relativas ao Moodle Provas';
$string['exam_actions:addinstance'] = 'Adiciona um novo bloco exam_actions';
$string['exam_actions:myaddinstance'] = 'Adiciona um novo bloco exam_actions à Página Inicial';
$string['exam_actions:supervise_exam'] = 'Gerar chave de acesso';
$string['exam_actions:monitor_exam'] = 'Visualizar relatórios de acompanhamento';

$string['title'] = 'Moodle Provas';
$string['no_permission'] = 'Você não tem permissão para realizar esta operação.';
$string['no_remote_course_found'] = 'Não foi localizado curso equivamente a: \'{$a->shortname}\' em: \'{$a->moodle}\'';

$string['groupings'] = 'Agrupamentos remotos e respectivos grupos:';
$string['groups'] = 'Grupos remotos não pertencentes a agrupamentos:';
$string['not_mapped_groups'] = 'Grupos locais sem correspondência no curso remoto:';

$string['review_permissions'] = 'Revisar minhas permissões';
$string['reviewed_permissions'] = 'Suas permissões foram revistas.';
$string['remote_courses'] = 'Cursos remotos';
$string['remote_courses_msg'] = 'Relação de cursos passíveis de serem disponibilizados no Moodle Provas.<br> 
Clique no nomes da categorias para visualizar os cursos relacionados e selecionar os cursos de seu interesse.';
$string['export_exam'] = 'Exportar atividades';
$string['export_exam_title'] = 'Exportar atividades e notas para: \'{$a}\'';
$string['export_exam_desc'] = 'Selecione abaixo as atividades a serem exportadas:';
$string['export'] = 'Exportar atividades selecionadas';
$string['export_result'] = 'Resultado da exportação';
$string['access_key'] = 'Chave de acesso';
$string['new_access_key'] = 'Gerar nova chave de acesso';
$string['generate_access_key'] = 'Gerar chave de acesso';
$string['generated_access_keys'] = 'Chaves de acesso geradas';
$string['used_access_keys'] = 'Uso das chaves de acesso';
$string['student_access'] = 'Acessos dos estudantes';
$string['release_computer'] = 'Liberar computador';
$string['release_this_computer'] = 'Liberar computador para realizar prova';
$string['monitor_exam'] = 'Monitorar prova';
$string['monitor_exam_title'] = 'Monitoramento de prova';
$string['anavaliable_course'] = 'Não é permitido gerar chaves de acesso para curso ocultos ou cuja data de início esteja no futuro.';

$string['sync_students'] = 'Sincronizar inscrições de estudantes';
$string['sync_students_title'] = 'Sincronização completada. Situação atual dos estudantes';
$string['sync_groups'] = 'Sincronizar grupos e membros';
$string['sync_groups_help'] = 'Os grupos e agrupamentos do Moodle remoto podem ser sincronizados no Moodle Provas, incluindo seus membros.
    Marque abaixo os grupos e agrupamentos que você deseja sincronizar. Observe, contudo, que o os grupos e os grupamentos existem de forma independente no Moodle.
        Assim, a seleção de um agrupamento não implica necessariamente que todos os seus grupos devam ser sincronizados. É necessário, portanto, indicar quais de seus grupos devam ser sincronizados.<br><br>
    A relação entre os grupos e agrupamentos locais e remotos é feito com base em seus nomes. Isto significa que se um nome for alterado,
    a relação entre os grupos se perderá, sendo necessário renomear um dos dois (local ou remoto) de forma a que voltem a ficar iguais.<br><br>
    Se já houver um grupo ou agrupamento local com o mesmo nome de um remoto, ele já aparecerá marcado, indicando que seus membros serão sincronizados.<br><br>
    Os grupos locais para os quais não haja o correspondente grupo no Moodle remoto aparecem listados no final.';
$string['sync_groups_button'] = 'Criar grupos/agrupamentos e sincronizar membros';
$string['sync_groups_title'] = 'Sincronizando grupos e agrupamentos';
$string['synced_groups_msg'] = 'Os grupos e agrupamentos foram criados e seus membros foram sincronizados';

$string['computer_released'] = 'Computador liberado para realizar prova: \'{$a}\'';
$string['new_course'] = 'Disponibilizar novo curso';
$string['release_courses'] = 'Disponibilizar cursos selecionados';

$string['no_remote_courses'] = 'Não foram localizados cursos remotos';
$string['no_remote_course'] = 'Não foi localizado curso remoto';
$string['no_course'] = 'Não foi localizado curso local: \'{$a}\'';
$string['confirm_release_courses'] = 'Você realmente deseja disponibilizar os cursos selecionados no Moodle Provas?';

$string['no_activities_to_export'] = 'Não há atividades a serem transpostas';
$string['no_selected_activities'] = 'Não foi selecionada nenhuma atividade a ser transposta para o Moodle remoto';
$string['no_selected_courses'] = 'Não foi selecionado nenhum curso a ser disponibilizado no Moodle Provas';

$string['already_added'] = ' (já disponível)';
$string['no_supervisor'] = 'Sem permissão para gerar chaves de acesso.';
$string['no_monitor'] = 'Sem permissão para monitorar provas.';
$string['no_editor'] = 'Sem permissão para editar ou disponibilizar curso: \'{$a->fullname}\' de: \'{$a->moodle}\'';
$string['generating_access_key'] = 'Gerando chave de acesso';
$string['generating_access_key_help'] = 'Chaves de acesso são necessárias para liberar os computadores que serão utilizados pelos estudantes para realizar provas.
    Elas são geradas por pessoas que tenha permissão para tal, estando vinculadas a cursos Moodle específicos. Isto significa que ao ser liberado, um computador 
    só pode ser utilizado para realizar prova do curso Moodle correspondente à chave utilizada na liberação.<br><br>
    Uma mesma chave pode ser utilizada para liberar vários computadores e podem ser geradas várias chaves durante a realização de uma prova
    (em caso de expiração do tempo de validade, por exemplo).<br><br>
    Para poder ser gerada uma chave de acesso, o curso deve estar visível sua data de início não pode estar no futuro.';
$string['no_course_to_generate_key'] = 'Não há cursos disponíveis/visíveis para os quais você possa gerar chave de acesso';

$string['course'] = 'Curso';
$string['course_help'] = 'Curso para o qual a chave de acesso é válida. Somente estudantes inscritos neste curso poderão ter acesso a ele utilizando essa chave.';
$string['select_course'] = '-- Selecione um curso';

$string['access_key_timeout'] = 'Validade da chave de acesso';
$string['access_key_timeout_help'] = 'Após este tempo a chave de acesso perde sua validade, ou seja, não poderá mais ser utilizada para liberar um computador para realizar prova.
    A autenticação do estudante também não será mais possível, mesmo que o computador já tenha sido liberado.';
$string['access_key_unknown'] = 'Chave de acesso desconhecida';
$string['access_key_timedout'] = 'Chave de acesso com validade expirada';

$string['header_version'] = 'Versão do CD';
$string['header_ip'] = 'Endereço IP local';
$string['header_network'] = 'Endereço de Rede local';
$string['real_ipaddress'] = 'Endereço IP real';
$string['real_ipaddress_generated'] = 'Endereço IP de geração';

$string['verify_client_host'] = 'Restrigir uso à rede local';
$string['verify_client_host_help'] = 'Verificar se o computador onde será utilizada a chave de acesso está na mesma rede do computador onde a chave foi gerada.';

$string['empty_course'] = 'É necessário selecionar o curso para o qual a chave de acesso é válida';
$string['empty_backup_file'] = 'Falha na geração do backup da atividade: arquivo vazio';
$string['activity_exported'] = 'Atividade foi transposta';

$string['cd_needed'] = 'É necessário utilizar o CD (ou pendrive) de provas para que este computador possa ser liberado para realizar uma prova.';
$string['invalid_cd_version'] = 'A versão do CD (ou pendrive) de provas é inválida ou muito antiga.';
$string['out_of_student_ip_ranges'] = 'Este computador não pode ser utilizado para realizar uma prova pois seu número IP está fora da faixa de IPs permitidos.';

$string['no_student_access_data'] = 'Não há dados de acessos de estudantes a serem apresentados';
$string['no_groups_to_sync'] = 'Não há grupos a sincronizar';

$string['access_keys'] = 'Chaves de acesso';
$string['used_by'] = 'Utilizada por';
$string['used_time'] = 'Utilizada em';
$string['createdon'] = 'Criada em';
$string['createdby'] = 'Criada por';

$string['kept'] = 'Mantido';
$string['enrolled'] = 'Inscrito';
$string['unenrolled'] = 'Desinscrito';

$string['manage_sessions'] = 'Gerenciar sessões de usuários';
$string['manage_sessions_title'] = 'Sessões dos usuários';
$string['delete_current_session'] = 'Sessão corrente não pode ser removida';
$string['unknown_session'] = 'Sessão desconhecida';
$string['confirm_delete_session'] = 'Você tem certeza que deseja remover a sessão de \'{$a}\'?<br><br><font color="red">O usuário não poderá mais salvar nenhum conteúdo e precisará autenticar-se novamente para poder acessar o ambiente.</font>';
$string['deleted_session'] = 'Sessão foi removida';

$string['no_function'] = 'Não foi identificada nenhuma função/papel com o qual neste momento você possa realizar ações sobre cursos neste ambiente.';
$string['warnings'] = 'Avisos';
$string['must_logout'] = 'Para liberar este computador para que um estudante realize prova é necessário que você saia (encerre sua sessão).
    <br><br>Encerrar sua sessão agora?';
?>
