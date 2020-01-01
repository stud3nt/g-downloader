import { ParserNode } from "./parser-node";
import { ParsedFile } from "./parsed-file";
import { Pagination } from "./pagination";

export class ParserRequest {

	constructor(obj?: any) {
		Object.assign(this, obj);

		this.files = [];
		this.parsedNodes = [];

		this.jumpPrevious = (obj && (obj.jumpPrevious === 'true' || obj.jumpPrevious === true));
		this.jumpNext = (obj && (obj.jumpNext === 'true' || obj.jumpNext === true));

		if (obj) {
			if (obj.currentNode) {
				this.currentNode = new ParserNode(obj.currentNode);
			}

			if (obj.files) {
				for (let parsedFile of obj.files) {
					this.files.push(new ParsedFile(parsedFile));
				}
			}

			if (obj.parsedNodes) {
				for (let parsedNode of obj.parsedNodes) {
					this.parsedNodes.push(new ParserNode(parsedNode));
				}
			}

			if (obj.pagination) {
				Object.assign(this.pagination, obj.pagination);
			}
		}
	}

	public scrollY: number = 0;

	public actionNode: ParserNode = null;

	public currentNode: ParserNode = new ParserNode();

	public parsedNodes: ParserNode[] = [];

	public files: ParsedFile[] = [];

	public fileData: ParsedFile = null;

	public jumpPrevious : boolean = false;
	public jumpNext : boolean = false;

	// parser name
	public parser: string;

	// current parser level
	public level: string;

	public pagination: Pagination = new Pagination();

	public tokens = {
		before: <string> null,
		after: <string> null
	};

	public sorting = {
		submit: <string> null,
		page: <number> 0
	};

	// ignore cache data (refreshing);
	public ignoreCache: boolean = false;

	public clearParsedData() : void {
		this.files = [];
		this.parsedNodes = [];
	}

	public resetNodes() {
		this.parsedNodes = [];
		this.currentNode = null;

		return this;
	}

	public resetPagination() {
		this.pagination = new Pagination();
		return this;
	}
	public resetSorting() {
		this.sorting = {
			submit: null,
			page: 0
		};

		return this;
	}

	public resetAll() {
		Object.assign(this, {});

		this.resetNodes()
			.resetPagination()
			.resetSorting();
	}

}