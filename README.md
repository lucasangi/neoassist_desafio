# Desafio desenvolvedor backend

Precisamos melhorar o atendimento no Brasil, para alcançar esse resultado, precisamos de um algoritmo que classifique
nossos tickets (disponível em tickets.json) por uma ordem de prioridade, um bom parâmetro para essa ordenação é identificar o humor do consumidor.
Pensando nisso, queremos classificar nossos tickets com as seguintes prioridade: Normal e Alta.

### São exemplos:

### Prioridade Alta:
- Consumidor insatisfeito com produto ou serviço
- Prazo de resolução do ticket alta
- Consumidor sugere abrir reclamação como exemplo Procon ou ReclameAqui
    
### Prioridade Normal
- Primeira iteração do consumidor
- Consumidor não demostra irritação

Considere uma classificação com uma assertividade de no mínimo 70%, e guarde no documento (Nosso json) a prioridade e sua pontuação.

### Com base nisso, você precisará desenvolver:
- Um algoritmo que classifique nossos tickets
- Uma API que exponha nossos tickets com os seguintes recursos
  - Ordenação por: Data Criação, Data Atualização e Prioridade
  - Filtro por: Data Criação (intervalo) e Prioridade
  - Paginação
        
### Escolha as melhores ferramentas para desenvolver o desafio, as únicas regras são:
- Você deverá fornecer informações para que possamos executar e avaliar o resultado;
- Poderá ser utilizado serviços pagos (Mas gostamos bastante de projetos open source)
    
### Critérios de avaliação
- Organização de código;
- Lógica para resolver o problema (Criatividade);
- Performance

## Instalação e Execução

### Ferramentas Necessárias
Para executar a aplicação é necessário ter o [Docker](https://www.docker.com/get-started) e o [Docker Compose](https://docs.docker.com/compose/) instalados em seu sistema operacional.

Caso o seu sistema seja um linux é recomendado a execução desses [comandos](https://docs.docker.com/install/linux/linux-postinstall/), a fim de que você possa executar comandos do docker sem a instrução __*sudo*__.

### Verificar Instalação das Ferramentas
Para verificar a o sucesso da instalação das ferramentas necessárias execute os seguintes comandos:
```
> docker --version
> docker-compose --version
```
A saída dos comandos devem representar respectivamente a versão do Docker e do Docker Compose. 

Por fim, vamos construir uma imagem de teste (hello-world) para confirmar o funcionamento Docker.
```
docker run hello-world
```
A saída do comando executado deve ser semelhante a esta:
```
docker : Unable to find image 'hello-world:latest' locally
...

latest:
Pulling from library/hello-world
ca4f61b1923c:
Pulling fs layer
ca4f61b1923c:
Download complete
ca4f61b1923c:
Pull complete
Digest: sha256:97ce6fa4b6cdc0790cda65fe7290b74cfebd9fa0c9b8c38e979330d547d22ce1
Status: Downloaded newer image for hello-world:latest

Hello from Docker!
This message shows that your installation appears to be working correctly.
...
```

Se a saída está semelhante ao exemplo acima, ótimo! Estamos prontos para começar :)

### Instalação da Aplicação
Para executar o ambiente da aplicação é necessário ir até a pasta do projeto e digitar o seguinte comando:
```
> docker-compose up -d
```
Esse comando irá realizar o _build_ do ambiente da aplicação que consiste em um _conteiner_ com [Apache](https://www.apache.org/) + [PHP 7.2.6](https://www.php.net/)  em conjunto com outro _container_ com o banco de dados orientado a documentos [MongoDB](https://www.mongodb.com/).

A instrução estará concluída quando algumas mensagens de _log_ aparecerem no seu console, conforme pode-se observar abaixo:
```
mongo    | 2019-06-25T12:32:21.071+0000 I NETWORK  [initandlisten] waiting for connections on port 27017
php      | [Tue Jun 25 12:32:20.732446 2019] [mpm_prefork:notice] [pid 1] AH00163: Apache/2.4.25 (Debian) PHP/7.2.6 configured -- resuming normal operations
```
Essas mensagens de _log_ são emitidas pelos _containers_ durante seu funcionamento. Isso indica que eles já foram construídos e estão funcionando :)

### Aplicação
Após a construção do ambiente da aplicação, basta acessar http://localhost:80 para visualizar o painel principal da aplicação. 

O painel possui 2 _cards_, o primeiro exibe algumas informações sobre o processo de classificação dos _tickets_ em conjunto com a resposta do serviço de classificação que pode ser requisitado manualmente através do arquivo **/services/classifier.php**.

O segundo quadro apresenta um formulário com campos de ordenação, paginação e filtros dos tickets. Para realizar uma requisição para a _API_ (localizada em **/services/api.php**) com os filtros escolhidos basta clicar no botão no cabeçalho do respectivo _card_ denominado de "Buscar". A resposta da requisição será exibida no quadro abaixo do formulário. 

### Classificador

A prioridade dos _tickets_ foi definida manualmente com base nas informações enviadas, os registros foram salvos no arquivo **/files/tickets.json** e a prioridade definida é representada pelo atributo **_priority_**.

O classificador implementado utiliza de dois pesos para realizar a predição: 
- Quantidade de palavras negativas encontradas nas interações;
- A porcentagem de tempo excedido em comparação ao tempo médio de um _ticket_ (36,8 dias).
> As palavras consideradas como negativas foram extraídas dos tickets após uma leitura manual. Pode-se visualizar as palavras na classe **system/Classifier.class.php**

Nesse contexto o classificador realiza a seguintes verificação:
- Se o texto tiver **mais de 3 palavras negativas** ou  o **tempo excedido for maior ou igual a 11%** o ticket é classificado como prioridade **Alta**, caso contrário, o será classificado como prioridade **Normal**.
> Durante o processo de classificação as informações utilizadas são armazenadas no atributo **classification** e a classe sugerida pelo classificador é representada pelo atributo **suggested_priority**.

Os _tickets_ classificados são salvos no arquivo **/files/classified_tickets.json** e no banco de documentos **MongoDB** para que possam ser consultados por intermédio da _API_.

### API
**URL**: /services/api.php
**Método**: GET
#### Parâmetros:
- filter: JSON
 ```
{
  "start": "2017-12-02",
  "end": "2017-12-30",
  "priority": "Normal"
}
```
- orderBy: JSON
 ```
{
  "field": "DateCreate",
  "mode": "DESC"
}
```
- pagination: JSON
 ```
{
  "qtd": "10",
  "page": "1"
}
```

#### Resposta:
```
{
  "pagination": {
    "total_of_tickets": 12,
    "number_of_pages": 2,
    "current_page": 1,
    "tickets_per_page": 10
  },
  "data": [
   {} ... {}
  ]
}
```