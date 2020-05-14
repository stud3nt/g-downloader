import { ParserNode } from "./parser-node";
import { ParsedFile } from "./parsed-file";
import { Pagination } from "./pagination";
import { BaseModel } from "./base/base-model";
import { Status } from "./status";
import { StatusCode } from "../enum/status-code";
import {Category} from "./category";
import {Tag} from "./tag";

export class ParserRequest extends BaseModel {

	constructor(obj?: any) {
		super();

		Object.assign(this, obj);

		this._files = [];
		this._parsedNodes = [];

		this.status = new Status();
		this.pagination = new Pagination(((obj && typeof obj.pagination !== 'undefined') ? obj.pagination : null));

		if (obj) {
			if (obj.currentNode)
				this._currentNode = new ParserNode(obj.currentNode);

			if (obj.files)
				for (let parsedFile of obj.files)
					this._files.push(new ParsedFile(parsedFile));

			if (obj.parsedNodes)
				for (let parsedNode of obj.parsedNodes)
					this._parsedNodes.push(new ParserNode(parsedNode));

			if (obj.nodesCategories)
				for (let cat of obj.nodesCategories)
					this._categories.push(new Category(cat));

			if (obj.status)
				Object.assign(this.status, obj.status);
		}
	}

	private _currentNode: ParserNode = null;

	private _parsedNodes: ParserNode[] = [];

	private _files: ParsedFile[] = [];

	private _nextNode: ParserNode = null;
	private _previousNode: ParserNode = null;

	// breadcrumbs - object with nodes
	private _breadcrumbNodes: ParserNode[] = [];

	private _pagination: Pagination = new Pagination(null);

	private _tokens = {
		before: <string> null,
		after: <string> null
	};

	private _sorting = {
		submit: <string> null,
		page: <number> 0
	};

	// ignore cache data (refreshing);
	private _ignoreCache: boolean = false;

	private _status: Status = null;

	// current api token
	private _apiToken: string = null;

	// request identifier
	private _requestIdentifier: string = null;

	private _categories: Category[] = [];

	private _tags: Tag[] = [];

	public onSuccess: () => any = null;
	public onError: (error) => any = null;
	public onComplete: () => any = null;

	public clearFilesDuplicates(): void {
		if (this._files.length < 1)
			return;

		let filesDataArray = [];

		for (let file of this._files) {
			if (typeof filesDataArray[file.identifier] === 'undefined')
				filesDataArray[file.identifier] = 1;
			else
				filesDataArray[file.identifier]++;
		}

		let reversedFiles = this._files.reverse();
		let clearedReversedFiles = [];

		for (let reversedFileKey in reversedFiles) {
			let file = reversedFiles[reversedFileKey];

			if (filesDataArray[file.identifier] === 1) {
				clearedReversedFiles.push(file);
				filesDataArray[file.identifier]--;
			} else if (filesDataArray[file.identifier] > 1) {
				filesDataArray[file.identifier]--;
			}
		}

		this._files = clearedReversedFiles.reverse();
	}

	public clearParsedData() : void {
		this._files = [];
		this._parsedNodes = [];
	}

	public resetNodes(): ParserRequest {
		this._parsedNodes = [];
		this._currentNode = null;

		return this;
	}

	public resetPagination(): ParserRequest {
		this._pagination = new Pagination();
		return this;
	}

	public resetSorting() {
		this._sorting = {
			submit: null,
			page: 0
		};

		return this;
	}

	public updateFile(modifiedFile: ParsedFile): void {
		if (this._files) {
			for (let fileKey in this._files) {
				let file = this._files[fileKey];

				if (file.identifier === modifiedFile.identifier)
					this._files[fileKey] = modifiedFile;
			}
		}
	}

	public clearTokens(): void {
		this.tokens = {
			before: null,
			after: null
		};
	}

	public findTagByName(tagName: string): (Tag|null) {
		if (this._tags)
			for (let tag of this._tags) {
				if (tagName.length > 2 && tag.name.toUpperCase() === tagName.toUpperCase())
					return tag;
			}
		else
			return null;
	}

	public isRequestDuplicated(): boolean {
		return (this._status.code === StatusCode.DuplicatedOperation);
	}

	public isRequestEnded(): boolean {
		return (this._status.code === StatusCode.OperationEnded);
	}

	public isRequestInProgress(): boolean {
		return (this._status.code === StatusCode.OperationInProgress);
	}

	public isRequestStarted(): boolean {
		return (this._status.code === StatusCode.OperationStarted);
	}

	public resetAll() {
		Object.assign(this, {});

		this.resetNodes()
			.resetPagination()
			.resetSorting();
	}

	get currentNode(): ParserNode {
		return this._currentNode;
	}

	set currentNode(value: ParserNode) {
		this._currentNode = value;
	}

	get parsedNodes(): ParserNode[] {
		return this._parsedNodes;
	}

	set parsedNodes(value: ParserNode[]) {
		this._parsedNodes = value;
	}

	get files(): ParsedFile[] {
		return this._files;
	}

	set files(value: ParsedFile[]) {
		this._files = value;
	}

	get nextNode(): ParserNode {
		return this._nextNode;
	}

	set nextNode(value: ParserNode) {
		this._nextNode = value;
	}

	get previousNode(): ParserNode {
		return this._previousNode;
	}

	set previousNode(value: ParserNode) {
		this._previousNode = value;
	}

	get breadcrumbNodes(): ParserNode[] {
		return this._breadcrumbNodes;
	}

	set breadcrumbNodes(value: ParserNode[]) {
		this._breadcrumbNodes = value;
	}

	get pagination(): Pagination {
		return this._pagination;
	}

	set pagination(value: Pagination) {
		this._pagination = value;
	}

	get tokens(): { before: string; after: string } {
		return this._tokens;
	}

	set tokens(value: { before: string; after: string }) {
		this._tokens = value;
	}

	get sorting(): { submit: string; page: number } {
		return this._sorting;
	}

	set sorting(value: { submit: string; page: number }) {
		this._sorting = value;
	}

	get ignoreCache(): boolean {
		return this._ignoreCache;
	}

	set ignoreCache(value: boolean) {
		this._ignoreCache = value;
	}

	get status(): Status {
		return this._status;
	}

	set status(value: Status) {
		this._status = value;
	}

	get apiToken(): string {
		return this._apiToken;
	}

	set apiToken(apiToken: string) {
		this._apiToken = apiToken;

		if (this._currentNode) {
			this._requestIdentifier = this._currentNode.parser+'_'+this._currentNode.level;

			if (this._currentNode.identifier)
				this._requestIdentifier += '_'+this._currentNode.identifier;

			this._requestIdentifier += '_'+apiToken
		}
	}

	get requestIdentifier(): string {
		return this._requestIdentifier;
	}

	set requestIdentifier(value: string) {
		this._requestIdentifier = value;
	}

	get categories(): Category[] {
		return this._categories;
	}

	set categories(value: Category[]) {
		this._categories = value;
	}

	get tags(): Tag[] {
		return this._tags;
	}

	set tags(value: Tag[]) {
		this._tags = value;
	}
}
