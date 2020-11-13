import { ParserLevelData } from "../model/parser-level-data";
import {ParsedNode} from "../model/parsed-node";

export class NodeLevel {

	static Gallery: string = 'gallery';
	static Board: string = 'board';
	static BoardsList: string = 'boards_list';
	static OwnerList: string = 'owner';

	static getData() : ParserLevelData[] {
		let parserLevelData: ParserLevelData[] = [{
			name: this.OwnerList,
			label: 'Users list',
			order: 4
		}, {
			name: this.BoardsList,
			label: 'Boards list',
			order: 3
		}, {
			name: this.Board,
			label: 'Galleries list',
			order: 2
		}, {
			name: this.Gallery,
			label: 'Gallery images',
			order: 1
		},];

		return parserLevelData;
	}

	static getLevelLabel(level: string = null) : string {
		switch (level) {
			case this.OwnerList:
				return 'Users list';

			case this.BoardsList:
				return 'Boards list';

			case this.Board:
				return 'Galleries list';

			case this.Gallery:
				return 'Gallery images';

			default:
				return '';
		}
	}

	static getIntLevel(level: string = null) : number {
		switch (level) {
			case this.OwnerList:
				return 4;

			case this.BoardsList:
				return 3;

			case this.Board:
				return 2;

			case this.Gallery:
				return 1;

			default:
				return null;
		}
	}

	static getNodeIntLevel(node: ParsedNode) : number {
		if (node.nextLevel) {
			return this.getIntLevel(node.nextLevel);
		}

		return this.getIntLevel(node.level);
	}

	static getDataKey(level: string = null) : string {
		switch (level) {
			case this.OwnerList:
				return 'owners';

			case this.BoardsList:
				return 'boards';

			case this.Board:
				return 'galleries';

			case this.Gallery:
				return 'files';
		}

		return null;
	}

	static getNextLevel(level: string) : string {
		let levelFound = false;
		let data = this.getData();

		for (let index in this.getData()) {
			let row = data[index];

			if (levelFound) {
				return row['name'];
			}

			if (row['name'] === level) {
				levelFound = true;
			}
		}

		return null;
	}
}
