export class ParserType {
	static Boards4Chan: string = 'boards_4chan';
	static Imagefap: string = 'imagefap';
	static Xhamster: string = 'xhamster';
	static Reddit: string = 'reddit';
	static HentaiFoundry: string = 'hentai_foundry';

	public static getData() : object
	{
		return {
			[this.Boards4Chan]: 'Boards 4Chan',
			[this.Imagefap] : 'imagefap.com',
			[this.Xhamster] : 'xhamster.com',
			[this.Reddit] : 'Reddit',
			[this.HentaiFoundry] : 'Hentai-Foundry'
		};
	}
}